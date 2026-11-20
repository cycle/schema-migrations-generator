<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Feature\Common;

use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Cycle\Schema\Generator\Migrations\NameBasedOnChangesGenerator;
use Cycle\Schema\Generator\Migrations\Strategy\MultipleFilesStrategy;
use Cycle\Schema\Generator\Migrations\Tests\GeneratorTestCase;
use Cycle\Testing\Database\Assert\SchemaAssert;
use Testo\Assert;
use Testo\Test;

final class MultipleFileStrategyTest extends GeneratorTestCase
{
    #[Test]
    public function testInit(): void
    {
        $tables = $this->migrate(self::FIXTURES . 'Init');

        Assert::count($this->migrator->getMigrations(), 3);
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        foreach ($tables as $t) {
            SchemaAssert::sameAsInDb($t);
        }
    }

    #[Test]
    public function testNoChanges(): void
    {
        $this->migrate(self::FIXTURES . 'Init');

        Assert::count($this->migrator->getMigrations(), 3);
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        $this->migrate(self::FIXTURES . 'Init');
        Assert::count($this->migrator->getMigrations(), 3);
    }

    #[Test]
    public function testAlter(): void
    {
        $tables = $this->migrate(self::FIXTURES . 'Init');

        Assert::count($this->migrator->getMigrations(), 3);
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        foreach ($tables as $t) {
            SchemaAssert::sameAsInDb($t);
        }

        $tables = $this->migrate(self::FIXTURES . 'Alter');

        Assert::count($this->migrator->getMigrations(), 6);
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        foreach ($tables as $t) {
            SchemaAssert::sameAsInDb($t);
        }
    }

    protected function getGenerateMigrations(): GenerateMigrations
    {
        $config = $this->migrator->getConfig();

        return new GenerateMigrations(
            $this->migrator->getRepository(),
            $config,
            new MultipleFilesStrategy($config, new NameBasedOnChangesGenerator()),
        );
    }
}
