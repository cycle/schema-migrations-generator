<div align="center">
    <a href="https://cycle-orm.dev" target="_blank">
        <picture>
            <source media="(prefers-color-scheme: dark)" srcset="https://github.com/cycle/.github/blob/main/logo/words-vector-dark.svg?raw=true">
            <img width="50%" align="center" src="https://github.com/cycle/.github/blob/main/logo/words-vector-light.svg?raw=true" alt="CycleORM Logo">
        </picture>
    </a>
</div>

<p align="center">Migration generator for Cycle ORM</p>

<div align="center">

[![Documentation](https://img.shields.io/badge/Documentation-blue?style=for-the-badge&logo=gitbook&logoColor=white)](https://cycle-orm.dev/docs/database-migrations/current/en)
[![Sponsor](https://img.shields.io/static/v1?style=for-the-badge&label=&message=Sponsor&logo=githubsponsors&logoColor=white&color=%23EA4AAA)](https://github.com/sponsors/cycle)
[![Discord](https://img.shields.io/discord/538114875570913290?style=for-the-badge&logo=discord&labelColor=7289d9&logoColor=white&color=39456d)](https://discord.gg/spiralphp)

[![Psalm Level](https://shepherd.dev/github/cycle/schema-migrations-generator/level.svg)](https://shepherd.dev/github/cycle/schema-migrations-generator)
[![Type Coverage](https://shepherd.dev/github/cycle/schema-migrations-generator/coverage.svg)](https://shepherd.dev/github/cycle/schema-migrations-generator)
[![Codecov](https://codecov.io/gh/cycle/schema-migrations-generator/branch/2.x/graph/badge.svg)](https://codecov.io/gh/cycle/schema-migrations-generator)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fcycle%2Fschema-migrations-generator%2F2.x)](https://dashboard.stryker-mutator.io/reports/github.com/cycle/schema-migrations-generator/2.x)

</div>

<br />

The migration generator creates a set of migration files for Cycle ORM during schema compilation. You can edit the generated migrations by hand before running them.

## Get Started

### Requirements

- PHP 8.2+

### Installation

```bash
composer require cycle/schema-migrations-generator
```

[![PHP](https://img.shields.io/packagist/php-v/cycle/schema-migrations-generator.svg?style=flat-square&logo=php)](https://packagist.org/packages/cycle/schema-migrations-generator)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/cycle/schema-migrations-generator.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/cycle/schema-migrations-generator)
[![License](https://img.shields.io/packagist/l/cycle/schema-migrations-generator.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/cycle/schema-migrations-generator.svg?style=flat-square)](https://packagist.org/packages/cycle/schema-migrations-generator/stats)

### Configuration

```php
use Cycle\Migrations;
use Cycle\Schema\Registry;
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

$config = new Migrations\Config\MigrationConfig([
    'directory' => __DIR__ . '/migrations/',
    'table' => 'migrations',
]);

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

### Running

The generator creates the migrations needed to sync the database schema with the desired state. Each database receives its own migration.

```php
$generator->run($registry);
```
