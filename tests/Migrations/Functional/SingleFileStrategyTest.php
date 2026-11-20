<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Functional;

abstract class SingleFileStrategyTest extends BaseTest
{
    #[\Testo\Test]
    public function testInit(): void
    {
        $tables = $this->migrate(__DIR__ . '/Fixtures/Init');

        \Testo\Assert::count($this->migrator->getMigrations(), 2);
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        foreach ($tables as $t) {
            $this->assertSameAsInDB($t);
        }
    }

    #[\Testo\Test]
    public function testNoChanges(): void
    {
        $this->migrate(__DIR__ . '/Fixtures/Init');

        \Testo\Assert::count($this->migrator->getMigrations(), 2);
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        $this->migrate(__DIR__ . '/Fixtures/Init');
        \Testo\Assert::count($this->migrator->getMigrations(), 2);
    }

    #[\Testo\Test]
    public function testAlter(): void
    {
        $tables = $this->migrate(__DIR__ . '/Fixtures/Init');

        \Testo\Assert::count($this->migrator->getMigrations(), 2);
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        foreach ($tables as $t) {
            $this->assertSameAsInDB($t);
        }

        $tables = $this->migrate(__DIR__ . '/Fixtures/Alter');

        \Testo\Assert::count($this->migrator->getMigrations(), 4);
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        foreach ($tables as $t) {
            $this->assertSameAsInDB($t);
        }
    }
}
