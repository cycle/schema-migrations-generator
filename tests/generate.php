<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

use Cycle\Schema\Generator\Migrations\Tests\Functional\BaseTest;
use Spiral\Tokenizer;

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '1');

//Composer
require dirname(__DIR__) . '/vendor/autoload.php';

$tokenizer = new Tokenizer\Tokenizer(new Tokenizer\Config\TokenizerConfig([
    'directories' => [__DIR__],
    'exclude' => [],
]));

$databases = [
    'sqlite' => [
        'namespace' => 'Cycle\Schema\Generator\Migrations\Tests\Functional\Driver\SQLite',
        'directory' => __DIR__ . '/Migrations/Functional/Driver/SQLite/',
    ],
    'mysql' => [
        'namespace' => 'Cycle\Schema\Generator\Migrations\Tests\Functional\Driver\MySQL',
        'directory' => __DIR__ . '/Migrations/Functional/Driver/MySQL/',
    ],
    'postgres' => [
        'namespace' => 'Cycle\Schema\Generator\Migrations\Tests\Functional\Driver\Postgres',
        'directory' => __DIR__ . '/Migrations/Functional/Driver/Postgres/',
    ],
    'sqlserver' => [
        'namespace' => 'Cycle\Schema\Generator\Migrations\Tests\Functional\Driver\SQLServer',
        'directory' => __DIR__ . '/Migrations/Functional/Driver/SQLServer/',
    ],
];

echo "Generating test classes for all database types...\n";

$classes = $tokenizer->classLocator()->getClasses(BaseTest::class);

foreach ($classes as $class) {
    if (!$class->isAbstract() || $class->getName() === BaseTest::class) {
        continue;
    }

    echo "Found {$class->getName()}\n";
    foreach ($databases as $driver => $details) {
        $filename = \sprintf('%s/%s.php', $details['directory'], $class->getShortName());

        file_put_contents(
            $filename,
            sprintf(
                '<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace %s;

use %s as CommonTestCase;

final class %s extends CommonTestCase
{
    const DRIVER = "%s";
}',
                $details['namespace'],
                $class->getName(),
                $class->getShortName(),
                $driver
            )
        );
    }
}

// helper to validate the selection results
// file_put_contents('out.php', '<?php ' . var_export($selector->fetchData(), true));
