<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Unit;

use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Schema\Generator\Migrations\MigrationImage;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\FileDeclaration;

class MigrationImageTest
{
    protected const DATABASE_DEFAULT = 'defaultDatabaseName';

    protected MigrationImage $migrationImage;
    protected static MigrationConfig $defaultMigrationConfig;

    public static function databaseData(): array
    {
        return [
            ['Default'],
            ['test'],
            [''],
        ];
    }

    public static function migrationNameData(): array
    {
        return [
            ['simple'],
            ['camelCase'],
            [''],
        ];
    }

    #[\Testo\Test]
    public function testRequires(): void
    {
        $class = $this->migrationImage->getClass();
        $constValue = $class->getConstant('DATABASE')->getValue();
        \Testo\Assert::equals($constValue, static::DATABASE_DEFAULT, 'Check the DATABASE constant');
        \Testo\Assert::false(empty($class->getName()), 'Class name is not empty');

        // check `up` and `down` methods
        $methods = $class->getMethods()->getIterator();
        $names = [];
        foreach ($methods as $method) {
            $names[] = $method->getName();
        }
        \Testo\Assert::contains($names, 'up', 'Method up() exists');
        \Testo\Assert::contains($names, 'down', 'Method down() exists');

        $file = $this->migrationImage->getFile();

        \Testo\Assert::true($file->getClasses()->has($class->getName()), 'The ClassDefinition exists in the FileDefinition');
    }

    #[\Testo\Test]
    public function testGetDatabase($database = self::DATABASE_DEFAULT): void
    {
        \Testo\Assert::equals($this->migrationImage->getDatabase(), $database, 'Test the database getter');
    }

    #[\Testo\Test]
    #[\Testo\Data\DataProvider('databaseData')]
    public function testSetDatabase(mixed $database): void
    {
        $this->migrationImage->setDatabase($database);

        $this->testGetDatabase($database);

        $constValue = $this->migrationImage->getClass()->getConstant('DATABASE')->getValue();
        \Testo\Assert::equals($constValue, $database, 'DATABASE constant changed in the class declaration');
    }

    #[\Testo\Test]
    public function testGetMigrationConfig(): void
    {
        \Testo\Assert::equals($this->migrationImage->getMigrationConfig(), static::$defaultMigrationConfig);
    }

    #[\Testo\Test]
    public function testGetClass(): void
    {
        \Testo\Assert::instanceOf($this->migrationImage->getClass(), ClassDeclaration::class);
    }

    #[\Testo\Test]
    public function testGetFile(): void
    {
        \Testo\Assert::instanceOf($this->migrationImage->getFile(), FileDeclaration::class);
    }

    #[\Testo\Test]
    public function testBuildFileName(): void
    {
        $this->substringInFileName(static::DATABASE_DEFAULT, '{database}', 'Default database name in the filename');

        $this->migrationImage->fileNamePattern = '';
        \Testo\Assert::equals($this->migrationImage->buildFileName(), '', 'Empty pattern');
    }

    /**
     * @param string $name Empty string by default because the migration name in the MigrationImage instance
     *                     is also empty string
     */
    #[\Testo\Test]
    public function testGetName(string $name = ''): void
    {
        \Testo\Assert::equals($this->migrationImage->getName(), $name, 'Test the name getter');
    }

    #[\Testo\Test]
    #[\Testo\Data\DataProvider('migrationNameData')]
    public function testSetName(mixed $name): void
    {
        $this->migrationImage->setName($name);

        $this->testGetName($name);

        if (strlen($name)) {
            $this->substringInFileName($name, '{name}', 'Migration name in the filename');
        }
    }

    #[\Testo\Lifecycle\BeforeClass]
    public static function setUpBeforeClass(): void
    {
        static::$defaultMigrationConfig = new MigrationConfig();
    }

    #[\Testo\Lifecycle\BeforeTest]
    public function setUp(): void
    {
        $this->migrationImage = new MigrationImage(static::$defaultMigrationConfig, static::DATABASE_DEFAULT);
    }

    protected function substringInFileName(string $substr, ?string $pattern = null, string $message = ''): void
    {
        if (is_string($pattern)) {
            $this->migrationImage->fileNamePattern = $pattern;
        }
        $fileName = $this->migrationImage->buildFileName();
        \Testo\Assert::string($fileName)->ignoringCase()->contains($substr, $message);
    }
}
