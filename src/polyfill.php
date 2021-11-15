<?php

/**
 * This file is part of Cycle ORM package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

spl_autoload_register(static function (string $class) {
    if ($class === Cycle\Migrations\GenerateMigrations::class || $class === Cycle\Migrations\MigrationImage::class) {
        $original = str_replace('Cycle\\Migrations', 'Cycle\\Schema\\Generator\\Migrations', $class);

        @trigger_error(
            "$class has been deprecated since cycle/schema-migrations-generator 1.0 " .
            "and will be removed in further release. Please use class $original instead.",
            E_USER_DEPRECATED
        );

        if (class_exists($original) && (!class_exists($class) && !interface_exists($class))) {
            class_alias($original, $class);
        }
    }
});
