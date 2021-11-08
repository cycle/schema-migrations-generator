<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations;

use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\Migration;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\FileDeclaration;

class MigrationImage
{
    public string $fileNamePattern = '{database}_{name}';
    protected ClassDeclaration $class;
    protected FileDeclaration $file;
    protected ?string $database = null;
    protected string $name = '';

    /**
     * MigrationImage constructor.
     */
    public function __construct(protected MigrationConfig $migrationConfig, string $database)
    {
        $this->class = new ClassDeclaration('newMigration', 'Migration');

        $this->class->method('up')->setReturn('void')->setPublic();
        $this->class->method('down')->setReturn('void')->setPublic();

        $this->file = new FileDeclaration($migrationConfig->getNamespace());
        $this->file->addUse(Migration::class);
        $this->file->addElement($this->class);

        $this->setDatabase($database);
    }

    public function getClass(): ClassDeclaration
    {
        return $this->class;
    }

    public function getFile(): FileDeclaration
    {
        return $this->file;
    }

    public function getMigrationConfig(): MigrationConfig
    {
        return $this->migrationConfig;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function setDatabase(string $database): void
    {
        $this->database = $database;

        $className = sprintf(
            'orm_%s_%s',
            $database,
            md5(microtime(true) . microtime(false))
        );
        $this->class->setName($className);

        $this->class->constant('DATABASE')->setProtected()->setValue($database);
    }

    public function buildFileName(): string
    {
        return str_replace(['{database}', '{name}'], [$this->database, $this->name], $this->fileNamePattern);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
