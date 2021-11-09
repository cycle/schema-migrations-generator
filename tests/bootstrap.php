<?php

/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '1');

use Cycle\Database\Config;

//Composer
require dirname(__DIR__) . '/vendor/autoload.php';

\Cycle\Schema\Generator\Migrations\Tests\BaseTest::$config = [
    'debug' => false,
    'strict' => true,
    'benchmark' => false,
    'sqlite' => new Config\SQLiteDriverConfig(
        queryCache: true,
    ),
    'mysql' => new Config\MySQLDriverConfig(
        connection: new Config\MySQL\TcpConnectionConfig(
            database: 'spiral',
            host: '127.0.0.1',
            port: 13306,
            user: 'root',
            password: 'root',
        ),
        queryCache: true
    ),
    'postgres' => new Config\PostgresDriverConfig(
        connection: new Config\Postgres\TcpConnectionConfig(
            database: 'spiral',
            host: '127.0.0.1',
            port: 15432,
            user: 'postgres',
            password: 'postgres',
        ),
        schema: 'public',
        queryCache: true,
    ),
    'sqlserver' => new Config\SQLServerDriverConfig(
        connection: new Config\SQLServer\TcpConnectionConfig(
            database: 'tempdb',
            host: '127.0.0.1',
            port: 11433,
            user: 'SA',
            password: 'SSpaSS__1'
        ),
        queryCache: true
    ),
];

if (!empty(getenv('DB'))) {
    switch (getenv('DB')) {
        case 'mariadb':
            \Cycle\Schema\Generator\Migrations\Tests\BaseTest::$config = [
                'debug' => false,
                'mysql' => new Config\MySQLDriverConfig(
                    connection: new Config\MySQL\TcpConnectionConfig(
                        database: 'spiral',
                        host: '127.0.0.1',
                        port: 23306,
                        user: 'root',
                        password: 'root',
                    ),
                    queryCache: true
                ),
            ];
            break;
    }
}
