<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations;

use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\Migration;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\FileDeclaration;
use Spiral\Reactor\Partial\PhpNamespace;

class MigrationImage
{
    public string $fileNamePattern = '{database}_{name}';
    protected ClassDeclaration $class;
    protected FileDeclaration $file;
    protected ?string $database = null;
    protected string $name = '';

    public function __construct(
        protected MigrationConfig $migrationConfig,
        string $database
    ) {
        $namespace = new PhpNamespace($migrationConfig->getNamespace());
        $namespace->addUse(Migration::class);

        $this->class = $namespace->addClass('newMigration');
        $this->class->setExtends(Migration::class);

        $this->class->addMethod('up')->setPublic()->setReturnType('void');
        $this->class->addMethod('down')->setPublic()->setReturnType('void');

        $this->file = new FileDeclaration();
        $this->file->addNamespace($namespace);

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

        if (!$this->class->getConstants()->has('DATABASE')) {
            $this->class->addConstant('DATABASE', $database)->setProtected();
        }
        $this->class->getConstant('DATABASE')->setValue($database);
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
