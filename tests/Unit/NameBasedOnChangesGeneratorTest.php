<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Unit;

use Cycle\Database\Driver\SQLite\Schema\SQLiteColumn;
use Cycle\Database\Schema\AbstractForeignKey;
use Cycle\Database\Schema\AbstractIndex;
use Cycle\Database\Schema\AbstractTable;
use Cycle\Database\Schema\ComparatorInterface;
use Cycle\Migrations\Atomizer\Atomizer;
use Cycle\Migrations\Atomizer\RendererInterface;
use Cycle\Schema\Generator\Migrations\NameBasedOnChangesGenerator;
use Testo\Assert;
use Testo\Test;

/**
 * @coversDefaultClass \Cycle\Schema\Generator\Migrations\NameBasedOnChangesGenerator
 */
#[Test]
final class NameBasedOnChangesGeneratorTest
{
    /**
     * @covers ::generate
     */
    public function testGenerate(): void
    {
        $atomizer = new Atomizer(
            \Mockery::mock(RendererInterface::class)->shouldIgnoreMissing(),
        );

        $create = \Mockery::mock(AbstractTable::class)->shouldIgnoreMissing();
        $create->shouldReceive('getName')->andReturn('creates');
        $create->shouldReceive('getStatus')->andReturn(AbstractTable::STATUS_NEW);

        $drops = \Mockery::mock(AbstractTable::class)->shouldIgnoreMissing();
        $drops->shouldReceive('getName')->andReturn('drops');
        $drops->shouldReceive('getStatus')->andReturn(AbstractTable::STATUS_DECLARED_DROPPED);

        $renamesCmp = \Mockery::mock(ComparatorInterface::class)->shouldIgnoreMissing();
        $renamesCmp->shouldReceive('isRenamed')->andReturn(true);
        $renames = \Mockery::mock(AbstractTable::class)->shouldIgnoreMissing();
        $renames->shouldReceive('getName')->andReturn('renames');
        $renames->shouldReceive('getStatus')->andReturn(AbstractTable::STATUS_EXISTS);
        $renames->shouldReceive('getComparator')->andReturn($renamesCmp);
        $renames->shouldReceive('getInitialName')->andReturn('old_name');

        $atomizer
            ->addTable($create)
            ->addTable($drops)
            ->addTable($renames);
        $generator = new NameBasedOnChangesGenerator();
        Assert::same($generator->generate($atomizer), 'create_creates_drop_drops_rename_old_name');
    }

    public function namesEveryChangeOfAnAlteredTableByItsCurrentName(): void
    {
        $atomizer = new Atomizer(
            \Mockery::mock(RendererInterface::class)->shouldIgnoreMissing(),
        );
        $comparator = \Mockery::mock(ComparatorInterface::class)->shouldIgnoreMissing();
        $comparator->shouldReceive('isRenamed')->andReturn(false);
        $comparator->shouldReceive('addedColumns')->andReturn([new SQLiteColumn('t', 'c1')]);
        $comparator->shouldReceive('droppedColumns')->andReturn([new SQLiteColumn('t', 'c2')]);
        $comparator->shouldReceive('alteredColumns')->andReturn([
            [new SQLiteColumn('t', 'c3'), new SQLiteColumn('t', 'c3_old')],
        ]);
        $comparator->shouldReceive('addedIndexes')->andReturn([$this->named(AbstractIndex::class, 'i1')]);
        $comparator->shouldReceive('droppedIndexes')->andReturn([$this->named(AbstractIndex::class, 'i2')]);
        $comparator->shouldReceive('alteredIndexes')->andReturn([
            [$this->named(AbstractIndex::class, 'i3'), $this->named(AbstractIndex::class, 'i3_old')],
        ]);
        $comparator->shouldReceive('addedForeignKeys')->andReturn([$this->named(AbstractForeignKey::class, 'fk1')]);
        $comparator->shouldReceive('droppedForeignKeys')->andReturn([$this->named(AbstractForeignKey::class, 'fk2')]);
        $comparator->shouldReceive('alteredForeignKeys')->andReturn([
            [$this->named(AbstractForeignKey::class, 'fk3'), $this->named(AbstractForeignKey::class, 'fk3_old')],
        ]);
        $table = \Mockery::mock(AbstractTable::class)->shouldIgnoreMissing();
        $table->shouldReceive('getName')->andReturn('users');
        $table->shouldReceive('getStatus')->andReturn(AbstractTable::STATUS_EXISTS);
        $table->shouldReceive('getComparator')->andReturn($comparator);
        $atomizer->addTable($table);

        $name = (new NameBasedOnChangesGenerator())->generate($atomizer);

        Assert::same(
            $name,
            'change_users_add_c1_rm_c2_alter_c3_add_index_i1_rm_index_i2_alter_index_i3'
            . '_add_fk_fk1_rm_fk_fk2_alter_fk_fk3',
        );
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function named(string $class, string $name): object
    {
        $mock = \Mockery::mock($class)->shouldIgnoreMissing();
        $mock->shouldReceive('getName')->andReturn($name);

        return $mock;
    }
}
