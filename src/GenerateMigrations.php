<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations;

use Cycle\Schema\Generator\Migrations\Exception\GeneratorException;
use Cycle\Schema\Generator\Migrations\Strategy\GeneratorStrategyInterface;
use Cycle\Schema\Generator\Migrations\Strategy\SingleFileStrategy;
use Cycle\Schema\Generator\SyncTables;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;
use Cycle\Database\Schema\AbstractTable;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\RepositoryInterface;

/**
 * Migration generator creates set of migrations needed to sync database schema with desired state. Each database will
 * receive it's own migration.
 */
class GenerateMigrations implements GeneratorInterface
{
    private GeneratorStrategyInterface $strategy;

    /**
     * @param MigrationConfig $migrationConfig deprecated param since v2.2.0.
     */
    public function __construct(
        private RepositoryInterface $repository,
        private MigrationConfig $migrationConfig,
        GeneratorStrategyInterface $strategy = null
    ) {
        $this->strategy = $strategy ?? new SingleFileStrategy($migrationConfig, new NameBasedOnChangesGenerator());
    }

    public function run(Registry $registry): Registry
    {
        $databases = [];
        foreach ($registry as $e) {
            if ($registry->hasTable($e) && !$e->getOptions()->has(SyncTables::READONLY_SCHEMA)) {
                $databases[$registry->getDatabase($e)][] = $registry->getTableSchema($e);
            }
        }

        foreach ($databases as $database => $tables) {
            foreach ($this->strategy->generate($database, $tables) as $image) {
                $class = $image->getClass()->getName();
                $name = \substr($image->buildFileName(), 0, 128);

                $this->repository->registerMigration($name, $class, (string) $image->getFile());
            }
        }

        return $registry;
    }

    /**
     * @param AbstractTable[] $tables
     *
     * @return array [string, FileDeclaration]
     *
     * @deprecated since v2.2.0
     */
    protected function generate(string $database, array $tables): ?MigrationImage
    {
        if (!$this->strategy instanceof SingleFileStrategy) {
            throw new GeneratorException('Only `SingleFileStrategy` is supported.');
        }

        $images = $this->strategy->generate($database, $tables);

        return \array_shift($images);
    }
}
