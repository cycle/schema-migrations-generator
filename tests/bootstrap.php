<?php

/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '1');

//Composer
require dirname(__DIR__) . '/vendor/autoload.php';

\Cycle\Schema\Generator\Migrations\Tests\BaseTest::$config = [
    'debug'     => false,
    'strict'    => true,
    'benchmark' => false,
    'sqlite'    => [
        'driver' => \Cycle\Database\Driver\SQLite\SQLiteDriver::class,
        'check'  => function () {
            return !in_array('sqlite', \PDO::getAvailableDrivers());
        },
        'conn'   => 'sqlite::memory:',
        'user'   => 'sqlite',
        'pass'   => ''
    ],
    'mysql'     => [
        'driver' => \Cycle\Database\Driver\MySQL\MySQLDriver::class,
        'check'  => function () {
            return !in_array('mysql', \PDO::getAvailableDrivers());
        },
        'conn'   => 'mysql:host=127.0.0.1:13306;dbname=spiral',
        'user'   => 'root',
        'pass'   => 'root'
    ],
    'postgres'  => [
        'driver' => \Cycle\Database\Driver\Postgres\PostgresDriver::class,
        'check'  => function () {
            return !in_array('pgsql', \PDO::getAvailableDrivers());
        },
        'conn'   => 'pgsql:host=127.0.0.1;port=15432;dbname=spiral',
        'user'   => 'postgres',
        'pass'   => 'postgres'
    ],
    'sqlserver' => [
        'driver' => \Cycle\Database\Driver\SQLServer\SQLServerDriver::class,
        'check'  => function () {
            return !in_array('sqlsrv', \PDO::getAvailableDrivers());
        },
        'conn'   => 'sqlsrv:Server=127.0.0.1,11433;Database=tempdb',
        'user'   => 'sa',
        'pass'   => 'SSpaSS__1'
    ],
];

if (!empty(getenv('DB'))) {
    switch (getenv('DB')) {
        case 'postgres':
            \Cycle\Schema\Generator\Migrations\Tests\BaseTest::$config = [
                'debug'    => false,
                'postgres' => [
                    'driver' => \Cycle\Database\Driver\Postgres\PostgresDriver::class,
                    'check'  => function () {
                        return true;
                    },
                    'conn'   => 'pgsql:host=127.0.0.1;port=5432;dbname=spiral',
                    'user'   => 'postgres',
                    'pass'   => 'postgres',
                ],
            ];
            break;

        case 'mariadb':
            \Cycle\Schema\Generator\Migrations\Tests\BaseTest::$config = [
                'debug' => false,
                'mysql' => [
                    'driver' => \Cycle\Database\Driver\MySQL\MySQLDriver::class,
                    'check'  => function () {
                        return true;
                    },
                    'conn'   => 'mysql:host=127.0.0.1:23306;dbname=spiral',
                    'user'   => 'root',
                    'pass'   => 'root',
                ],
            ];
            break;
    }
}
