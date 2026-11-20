<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Unit;

use Cycle\Database\Schema\AbstractTable;
use Cycle\Database\Schema\ComparatorInterface;
use Cycle\Migrations\Atomizer\Atomizer;
use Cycle\Migrations\Atomizer\RendererInterface;
use Cycle\Schema\Generator\Migrations\NameBasedOnChangesGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Cycle\Schema\Generator\Migrations\NameBasedOnChangesGenerator
 */
final class NameBasedOnChangesGeneratorTest
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

        $atomizer
            ->addTable($create)
            ->addTable($drops)
            ->addTable($renames);
        $generator = new NameBasedOnChangesGenerator();
        \Testo\Assert::same($generator->generate($atomizer), 'create_creates_drop_drops_rename_old_name');
    }
}
