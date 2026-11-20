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
use Cycle\Schema\Generator\Migrations\ChangesCountNameGenerator;

/**
 * @coversDefaultClass \Cycle\Schema\Generator\Migrations\ChangesCountNameGenerator
 */
final class ChangesCountTest
{
    /**
     * @covers ::generate
     */
    #[\Testo\Test]
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

        // Comparator
        $changeCmp = \Mockery::mock(ComparatorInterface::class)->shouldIgnoreMissing();
        $changeCmp->shouldReceive('isRenamed')->andReturn(false);
        //// Column
        $columnAdd = new SQLiteColumn('table', 'c1');
        $columnDrop = new SQLiteColumn('table', 'c2');
        $columnAlter = new SQLiteColumn('table', 'c3');
        $changeCmp->shouldReceive('addedColumns')->andReturn([$columnAdd]);
        $changeCmp->shouldReceive('droppedColumns')->andReturn([$columnDrop]);
        $changeCmp->shouldReceive('alteredColumns')->andReturn([
            [$columnAlter, new SQLiteColumn('table', 'altered')],
        ]);
        //// Index
        $indexAdd = \Mockery::mock(AbstractIndex::class)->shouldIgnoreMissing();
        $indexAdd->shouldReceive('getName')->andReturn('i1');
        $indexDrop = \Mockery::mock(AbstractIndex::class)->shouldIgnoreMissing();
        $indexDrop->shouldReceive('getName')->andReturn('i2');
        $indexAlter = \Mockery::mock(AbstractIndex::class)->shouldIgnoreMissing();
        $indexAlter->shouldReceive('getName')->andReturn('i3');
        $changeCmp->shouldReceive('addedIndexes')->andReturn([$indexAdd]);
        $changeCmp->shouldReceive('droppedIndexes')->andReturn([$indexDrop]);
        $changeCmp->shouldReceive('alteredIndexes')->andReturn([
            [$indexAlter, \Mockery::mock(AbstractIndex::class)->shouldIgnoreMissing()],
        ]);
        //// FK
        $fkAdd = \Mockery::mock(AbstractForeignKey::class)->shouldIgnoreMissing();
        $fkAdd->shouldReceive('getName')->andReturn('fk1');
        $fkDrop = \Mockery::mock(AbstractForeignKey::class)->shouldIgnoreMissing();
        $fkDrop->shouldReceive('getName')->andReturn('fk2');
        $fkAlter = \Mockery::mock(AbstractForeignKey::class)->shouldIgnoreMissing();
        $fkAlter->shouldReceive('getName')->andReturn('fk3');
        $changeCmp->shouldReceive('addedForeignKeys')->andReturn([$fkAdd]);
        $changeCmp->shouldReceive('droppedForeignKeys')->andReturn([$fkDrop]);
        $changeCmp->shouldReceive('alteredForeignKeys')->andReturn([
            [$fkAlter, \Mockery::mock(AbstractForeignKey::class)->shouldIgnoreMissing()],
        ]);

        // Table
        $change = \Mockery::mock(AbstractTable::class)->shouldIgnoreMissing();
        $change->shouldReceive('getStatus')->andReturn(AbstractTable::STATUS_EXISTS);
        $renames->shouldReceive('getName')->andReturn('changes');
        $change->shouldReceive('getComparator')->andReturn($changeCmp);

        $atomizer
            ->addTable($create)
            ->addTable($drops)
            ->addTable($renames)
            ->addTable($change);
        $generator = new ChangesCountNameGenerator();
        \Testo\Assert::same($generator->generate($atomizer), 'ct1_dt1_t2_c3_i3_fk3');
    }
}
