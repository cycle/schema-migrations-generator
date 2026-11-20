<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Unit;

use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\RepositoryInterface;
use Cycle\Schema\Generator\Migrations\Exception\GeneratorException;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Cycle\Schema\Generator\Migrations\MigrationImage;
use Cycle\Schema\Generator\Migrations\NameGeneratorInterface;
use Cycle\Schema\Generator\Migrations\Strategy\MultipleFilesStrategy;
use Cycle\Testing\Database\Memory;
use Testo\Assert;
use Testo\Expect;
use Testo\Test;

#[Test]
final class GenerateMigrationsTest
{
    public function testGenerateException(): void
    {
        $generator = new GenerateMigrations(
            \Mockery::mock(RepositoryInterface::class)->shouldIgnoreMissing(),
            new MigrationConfig(),
            new MultipleFilesStrategy(new MigrationConfig(), \Mockery::mock(NameGeneratorInterface::class)->shouldIgnoreMissing()),
        );

        $ref = new \ReflectionMethod($generator, 'generate');

        Expect::exception(GeneratorException::class);
        $ref->invoke($generator, 'test', []);
    }

    public function deprecatedGenerateReturnsTheSingleImage(): void
    {
        $users = Memory::database()->table('users')->getSchema();
        $users->primary('id');

        $image = $this->generate('default', [$users]);

        Assert::instanceOf($image, MigrationImage::class);
        Assert::same($image->getDatabase(), 'default');
        Assert::string((string) $image->getFile())->contains("\$this->table('users')");
    }

    public function deprecatedGenerateReturnsNullWithoutChanges(): void
    {
        $users = Memory::database()->table('users')->getSchema();

        Assert::null($this->generate('default', [$users]));
    }

    private function generate(string $database, array $tables): ?MigrationImage
    {
        $generator = new GenerateMigrations(
            \Mockery::mock(RepositoryInterface::class)->shouldIgnoreMissing(),
            new MigrationConfig(),
        );

        return (new \ReflectionMethod($generator, 'generate'))->invoke($generator, $database, $tables);
    }
}
