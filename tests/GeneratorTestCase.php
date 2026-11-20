<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests;

use Cycle\Annotated\Entities;
use Cycle\Annotated\Locator\TokenizerEntityLocator;
use Cycle\Annotated\MergeColumns;
use Cycle\Annotated\MergeIndexes;
use Cycle\Database\Schema\AbstractTable;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\FileRepository;
use Cycle\Migrations\Migrator;
use Cycle\Schema\Compiler;
use Cycle\Schema\Generator\GenerateRelations;
use Cycle\Schema\Generator\GenerateTypecast;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Cycle\Schema\Generator\RenderRelations;
use Cycle\Schema\Generator\RenderTables;
use Cycle\Schema\Generator\ResetTables;
use Cycle\Schema\Generator\ValidateEntities;
use Cycle\Schema\Registry;
use Cycle\Testing\Database\DatabaseTestCase;
use Spiral\Attributes\AttributeReader;
use Spiral\Core\Container;
use Spiral\Files\Files;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeTest;

/**
 * Compiles annotated entities into migrations written to `tests/files/` and runs them on the suite's database.
 */
abstract class GeneratorTestCase extends DatabaseTestCase
{
    protected const FILES = __DIR__ . '/files/';
    protected const FIXTURES = __DIR__ . '/Fixtures/';

    protected Migrator $migrator;
    private string $directory;

    #[BeforeTest]
    public function createMigrator(): void
    {
        # A directory per test: migration file names repeat within a second, and FileRepository loads them with
        # require_once, which skips a path already loaded by an earlier test of the same process.
        $this->directory = self::FILES . \uniqid('migrations_', true);
        (new Files())->ensureDirectory($this->directory);
        $config = new MigrationConfig([
            'directory' => $this->directory,
            'table' => 'migrations',
            'safe' => true,
            'namespace' => 'Migration',
        ]);
        $this->migrator = new Migrator($config, $this->dbal, new FileRepository($config, new Container()));
        $this->migrator->configure();
    }

    #[AfterTest]
    public function removeMigrationFiles(): void
    {
        (new Files())->deleteDirectory($this->directory);
    }

    /**
     * Compile the entities of the directory and generate migrations for the changes.
     *
     * @return list<AbstractTable> The declared table schemas.
     */
    protected function migrate(string $directory): array
    {
        $locator = (new Tokenizer(new TokenizerConfig(['directories' => [$directory], 'exclude' => []])))->classLocator();
        $reader = new AttributeReader();
        $registry = new Registry($this->dbal);

        (new Compiler())->compile($registry, [
            new Entities(new TokenizerEntityLocator($locator, $reader), $reader),
            new ResetTables(),
            new MergeColumns($reader),
            new GenerateRelations(),
            new ValidateEntities(),
            new RenderTables(),
            new RenderRelations(),
            new MergeIndexes($reader),
            new GenerateTypecast(),
            $this->getGenerateMigrations(),
        ]);

        $tables = [];
        foreach ($registry as $entity) {
            $tables[] = $registry->getTableSchema($entity);
        }

        return $tables;
    }

    protected function getGenerateMigrations(): GenerateMigrations
    {
        return new GenerateMigrations($this->migrator->getRepository(), $this->migrator->getConfig());
    }
}
