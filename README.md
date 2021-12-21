# Cycle ORM - Migration Generator

[![Latest Stable Version](https://poser.pugx.org/cycle/schema-migrations-generator/version)](https://packagist.org/packages/cycle/schema-migrations-generator)
[![Build Status](https://github.com/cycle/schema-migrations-generator/workflows/build/badge.svg)](https://github.com/cycle/schema-migrations-generator/actions)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cycle/schema-migrations-generator/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/cycle/schema-migrations-generator/?branch=2.x)
[![Codecov](https://codecov.io/gh/cycle/schema-migrations-generator/graph/badge.svg)](https://codecov.io/gh/cycle/schema-migrations-generator)

By migration generator package you can automatically generate a set of migration files for Cycle ORM during schema
compilation. In this case, you have the freedom to alter such migrations manually before running them.

## Installation

```bash
composer require cycle/schema-migrations-generator
```

## Configuration

```php
use Cycle\Migrations;
use Cycle\Schema\Registry;
use Cycle\Schema\Definition\Entity;
use Cycle\Database;
use Cycle\Database\Config;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;

$dbal = new Database\DatabaseManager(new Config\DatabaseConfig([
    'default' => 'default',
    'databases' => [
        'default' => [
            'connection' => 'sqlite'
        ]
    ],
    'connections' => [
        'sqlite' => new Config\SQLiteDriverConfig(
            connection: new Config\SQLite\MemoryConnectionConfig(),
            queryCache: true,
        ),
    ]
]));

$migrator = new Migrations\Migrator(
    $config, 
    $dbal, 
    new Migrations\FileRepository($config)
);

$registry = new Registry($dbal);
$registry->register(....);

$generator = new GenerateMigrations(
    $migrator->getRepository(), 
    $migrator->getConfig()
);
```

## Running

Migration generator creates set of migrations needed to sync database schema with desired state. Each database will
receive its own migration.

```php
$generator->run($registry);
```

## License:

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained
by [Spiral Scout](https://spiralscout.com).

