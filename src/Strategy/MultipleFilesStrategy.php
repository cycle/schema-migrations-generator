<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Strategy;

use Cycle\Database\Schema\AbstractTable;
use Cycle\Migrations\Atomizer\Atomizer;
use Cycle\Migrations\Atomizer\Renderer;
use Cycle\Migrations\Atomizer\TableSorter;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Schema\Generator\Migrations\MigrationImage;
use Cycle\Schema\Generator\Migrations\NameGeneratorInterface;

final class MultipleFilesStrategy implements GeneratorStrategyInterface
{
    private static int $sec = 0;

    public function __construct(
        private readonly MigrationConfig $migrationConfig,
        private readonly NameGeneratorInterface $nameGenerator,
        private readonly TableSorter $tableSorter = new TableSorter()
    ) {
    }

    /**
     * @param non-empty-string $database
     * @param array<AbstractTable> $tables
     *
     * @return array<MigrationImage>
     */
    public function generate(string $database, array $tables): array
    {
        $atomizer = new Atomizer(new Renderer());

        $images = [];
        foreach ($this->tableSorter->sort($tables) as $table) {
            if ($table->getComparator()->hasChanges()) {
                $atomizer->setTables([$table]);

                $image = new MigrationImage($this->migrationConfig, $database);
                $image->setName($this->nameGenerator->generate($atomizer));
                $image->fileNamePattern = self::$sec++ . '_{database}_{name}';

                $atomizer->declareChanges($image->getClass()->getMethod('up'));
                $atomizer->revertChanges($image->getClass()->getMethod('down'));

                $images[] = $image;
            }
        }

        return $images;
    }
}
