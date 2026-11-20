<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Feature\Common;

use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\RepositoryInterface;
use Cycle\Schema\Definition\Entity;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Cycle\Schema\Generator\Migrations\MigrationImage;
use Cycle\Schema\Generator\Migrations\Strategy\GeneratorStrategyInterface;
use Cycle\Schema\Generator\SyncTables;
use Cycle\Schema\Registry;
use Cycle\Testing\Database\DatabaseTestCase;
use Testo\Assert;
use Testo\Test;

#[Test]
final class GenerateMigrationsTest extends DatabaseTestCase
{
    public function truncatesTheMigrationNameTo128Characters(): void
    {
        $registry = new Registry($this->dbal);
        $this->linkedEntity($registry, 'user', 'users');
        $registered = [];
        $repository = \Mockery::mock(RepositoryInterface::class);
        $repository->shouldReceive('registerMigration')->andReturnUsing(
            static function (string $name) use (&$registered): string {
                $registered[] = $name;
                return $name;
            },
        );
        $strategy = $this->strategy(\str_repeat('a', 100) . \str_repeat('b', 100));

        (new GenerateMigrations($repository, new MigrationConfig(), $strategy))->run($registry);

        Assert::same($registered, [\str_repeat('a', 100) . \str_repeat('b', 28)]);
    }

    public function passesOnlyWritableTablesToTheStrategy(): void
    {
        $registry = new Registry($this->dbal);
        $this->linkedEntity($registry, 'user', 'users');
        $this->linkedEntity($registry, 'log', 'logs')->getOptions()->set(SyncTables::READONLY_SCHEMA, true);
        $registry->register((new Entity())->setRole('embedded'));
        $strategy = $this->strategy('name');

        (new GenerateMigrations(\Mockery::spy(RepositoryInterface::class), new MigrationConfig(), $strategy))
            ->run($registry);

        Assert::same($strategy->received, [$this->database->getName() => ['users']]);
    }

    private function linkedEntity(Registry $registry, string $role, string $table): Entity
    {
        $entity = (new Entity())->setRole($role);
        $registry->register($entity);
        $registry->linkTable($entity, $this->database->getName(), $table);

        return $entity;
    }

    private function strategy(string $name): GeneratorStrategyInterface
    {
        return new class($name) implements GeneratorStrategyInterface {
            /** @var array<string, list<string>> */
            public array $received = [];

            public function __construct(private readonly string $name) {}

            public function generate(string $database, array $tables): array
            {
                foreach ($tables as $table) {
                    $this->received[$database][] = $table->getName();
                }
                $image = new MigrationImage(new MigrationConfig(), $database);
                $image->setName($this->name);
                $image->fileNamePattern = '{name}';

                return [$image];
            }
        };
    }
}
