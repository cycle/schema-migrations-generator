<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Strategy;

use Cycle\Database\Schema\AbstractTable;
use Cycle\Schema\Generator\Migrations\MigrationImage;

interface GeneratorStrategyInterface
{
    /**
     * @param non-empty-string $database
     * @param array<AbstractTable> $tables
     *
     * @return array<MigrationImage>
     */
    public function generate(string $database, array $tables): array;
}
