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
    public function testInit(): void
    {
        $tables = $this->migrate(__DIR__ . '/Fixtures/Init');

        $this->assertCount(2, $this->migrator->getMigrations());
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

        $this->assertCount(2, $this->migrator->getMigrations());
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        $this->migrate(__DIR__ . '/Fixtures/Init');
        $this->assertCount(2, $this->migrator->getMigrations());
    }

    public function testAlter(): void
    {
        $tables = $this->migrate(__DIR__ . '/Fixtures/Init');

        $this->assertCount(2, $this->migrator->getMigrations());
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        foreach ($tables as $t) {
            $this->assertSameAsInDB($t);
        }

        $tables = $this->migrate(__DIR__ . '/Fixtures/Alter');

        $this->assertCount(4, $this->migrator->getMigrations());
        foreach ($this->migrator->getMigrations() as $m) {
            $this->migrator->run();
        }

        foreach ($tables as $t) {
            $this->assertSameAsInDB($t);
        }
    }
}
