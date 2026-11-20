<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Unit;

use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\RepositoryInterface;
use Cycle\Schema\Generator\Migrations\Exception\GeneratorException;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Cycle\Schema\Generator\Migrations\NameGeneratorInterface;
use Cycle\Schema\Generator\Migrations\Strategy\MultipleFilesStrategy;
use Mockery;
use Testo\Expect;
use Testo\Test;

#[Test]
final class GenerateMigrationsTest
{
    public function testGenerateException(): void
    {
        $generator = new GenerateMigrations(
            Mockery::mock(RepositoryInterface::class)->shouldIgnoreMissing(),
            new MigrationConfig(),
            new MultipleFilesStrategy(new MigrationConfig(), Mockery::mock(NameGeneratorInterface::class)->shouldIgnoreMissing()),
        );

        $ref = new \ReflectionMethod($generator, 'generate');

        Expect::exception(GeneratorException::class);
        $ref->invoke($generator, 'test', []);
    }
}
