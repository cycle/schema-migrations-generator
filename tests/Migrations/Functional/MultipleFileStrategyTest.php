<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Functional;

use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Cycle\Schema\Generator\Migrations\NameBasedOnChangesGenerator;
use Cycle\Schema\Generator\Migrations\Strategy\MultipleFilesStrategy;

abstract class MultipleFileStrategyTest extends BaseTest
{
    public function testInit(): void
    {
        $tables = $this->migrate(__DIR__ . '/Fixtures/Init');

        $this->assertCount(3, $this->migrator->getMigrations());
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        foreach ($tables as $t) {
            $this->assertSameAsInDB($t);
        }
    }

    public function testNoChanges(): void
    {
        $this->migrate(__DIR__ . '/Fixtures/Init');

        $this->assertCount(3, $this->migrator->getMigrations());
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        $this->migrate(__DIR__ . '/Fixtures/Init');
        $this->assertCount(3, $this->migrator->getMigrations());
    }

    public function testAlter(): void
    {
        $tables = $this->migrate(__DIR__ . '/Fixtures/Init');

        $this->assertCount(3, $this->migrator->getMigrations());
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        foreach ($tables as $t) {
            $this->assertSameAsInDB($t);
        }

        $tables = $this->migrate(__DIR__ . '/Fixtures/Alter');

        $this->assertCount(6, $this->migrator->getMigrations());
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        foreach ($tables as $t) {
            $this->assertSameAsInDB($t);
        }
    }

    protected function getGenerateMigrations(): GenerateMigrations
    {
        $config = new MigrationConfig(static::CONFIG);

        return new GenerateMigrations(
            $this->migrator->getRepository(),
            $config,
            new MultipleFilesStrategy($config, new NameBasedOnChangesGenerator())
        );
    }
}
