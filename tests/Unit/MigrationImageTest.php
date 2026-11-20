<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Unit;

use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Schema\Generator\Migrations\MigrationImage;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\FileDeclaration;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Lifecycle\BeforeClass;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class MigrationImageTest
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

    public function testRequires(): void
    {
        $class = $this->migrationImage->getClass();
        $constValue = $class->getConstant('DATABASE')->getValue();
        Assert::equals($constValue, self::DATABASE_DEFAULT, 'Check the DATABASE constant');
        Assert::false(empty($class->getName()), 'Class name is not empty');

        // check `up` and `down` methods
        $methods = $class->getMethods()->getIterator();
        $names = [];
        foreach ($methods as $method) {
            $names[] = $method->getName();
        }
        Assert::contains($names, 'up', 'Method up() exists');
        Assert::contains($names, 'down', 'Method down() exists');

        $file = $this->migrationImage->getFile();

        Assert::true($file->getClasses()->has($class->getName()), 'The ClassDefinition exists in the FileDefinition');
    }

    public function testGetDatabase($database = self::DATABASE_DEFAULT): void
    {
        Assert::equals($this->migrationImage->getDatabase(), $database, 'Test the database getter');
    }

    #[DataProvider('databaseData')]
    public function testSetDatabase(mixed $database): void
    {
        $this->migrationImage->setDatabase($database);

        $this->testGetDatabase($database);

        $constValue = $this->migrationImage->getClass()->getConstant('DATABASE')->getValue();
        Assert::equals($constValue, $database, 'DATABASE constant changed in the class declaration');
    }

    public function testGetMigrationConfig(): void
    {
        Assert::equals($this->migrationImage->getMigrationConfig(), self::$defaultMigrationConfig);
    }

    public function testGetClass(): void
    {
        Assert::instanceOf($this->migrationImage->getClass(), ClassDeclaration::class);
    }

    public function testGetFile(): void
    {
        Assert::instanceOf($this->migrationImage->getFile(), FileDeclaration::class);
    }

    public function testBuildFileName(): void
    {
        $this->substringInFileName(self::DATABASE_DEFAULT, '{database}', 'Default database name in the filename');

        $this->migrationImage->fileNamePattern = '';
        Assert::equals($this->migrationImage->buildFileName(), '', 'Empty pattern');
    }

    /**
     * @param string $name Empty string by default because the migration name in the MigrationImage instance
     *                     is also empty string
     */
    public function testGetName(string $name = ''): void
    {
        Assert::equals($this->migrationImage->getName(), $name, 'Test the name getter');
    }

    #[DataProvider('migrationNameData')]
    public function testSetName(mixed $name): void
    {
        $this->migrationImage->setName($name);

        $this->testGetName($name);

        if (strlen($name)) {
            $this->substringInFileName($name, '{name}', 'Migration name in the filename');
        }
    }

    #[BeforeClass]
    public static function setUpBeforeClass(): void
    {
        self::$defaultMigrationConfig = new MigrationConfig();
    }

    #[BeforeTest]
    public function setUp(): void
    {
        $this->migrationImage = new MigrationImage(self::$defaultMigrationConfig, self::DATABASE_DEFAULT);
    }

    protected function substringInFileName(string $substr, ?string $pattern = null, string $message = ''): void
    {
        if (is_string($pattern)) {
            $this->migrationImage->fileNamePattern = $pattern;
        }
        $fileName = $this->migrationImage->buildFileName();
        Assert::string($fileName)->ignoringCase()->contains($substr, $message);
    }
}
