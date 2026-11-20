<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Feature\Common;

use Cycle\Schema\Generator\Migrations\Tests\GeneratorTestCase;
use Cycle\Testing\Database\Assert\SchemaAssert;

final class SingleFileStrategyTest extends GeneratorTestCase
{
    #[\Testo\Test]
    public function testInit(): void
    {
        $tables = $this->migrate(self::FIXTURES . 'Init');

        \Testo\Assert::count($this->migrator->getMigrations(), 2);
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        foreach ($tables as $t) {
            SchemaAssert::sameAsInDb($t);
        }
    }

    #[\Testo\Test]
    public function testNoChanges(): void
    {
        $this->migrate(self::FIXTURES . 'Init');

        \Testo\Assert::count($this->migrator->getMigrations(), 2);
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        $this->migrate(self::FIXTURES . 'Init');
        \Testo\Assert::count($this->migrator->getMigrations(), 2);
    }

    #[\Testo\Test]
    public function testAlter(): void
    {
        $tables = $this->migrate(self::FIXTURES . 'Init');

        \Testo\Assert::count($this->migrator->getMigrations(), 2);
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        foreach ($tables as $t) {
            SchemaAssert::sameAsInDb($t);
        }

        $tables = $this->migrate(self::FIXTURES . 'Alter');

        \Testo\Assert::count($this->migrator->getMigrations(), 4);
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        foreach ($tables as $t) {
            SchemaAssert::sameAsInDb($t);
        }
    }
}
