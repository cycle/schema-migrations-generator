<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Unit;

use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\RepositoryInterface;
use Cycle\Schema\Generator\Migrations\Exception\GeneratorException;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Cycle\Schema\Generator\Migrations\NameGeneratorInterface;
use Cycle\Schema\Generator\Migrations\Strategy\MultipleFilesStrategy;
use PHPUnit\Framework\TestCase;

final class GenerateMigrationsTest extends TestCase
{
    public function testGenerateException(): void
    {
        $generator = new GenerateMigrations(
            $this->createMock(RepositoryInterface::class),
            new MigrationConfig(),
            new MultipleFilesStrategy(new MigrationConfig(), $this->createMock(NameGeneratorInterface::class))
        );

        $ref = new \ReflectionMethod($generator, 'generate');

        $this->expectException(GeneratorException::class);
        $ref->invoke($generator, 'test', []);
    }
}
