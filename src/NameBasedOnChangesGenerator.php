<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations;

use Cycle\Database\Schema\AbstractTable;
use Cycle\Migrations\Atomizer\Atomizer;

final class NameBasedOnChangesGenerator implements NameGeneratorInterface
{
    public function generate(Atomizer $atomizer): string
    {
        $name = [];

        foreach ($atomizer->getTables() as $table) {
            if ($table->getStatus() === AbstractTable::STATUS_NEW) {
                $name[] = 'create_' . $table->getName();
                continue;
            }

            if ($table->getStatus() === AbstractTable::STATUS_DECLARED_DROPPED) {
                $name[] = 'drop_' . $table->getName();
                continue;
            }

            if ($table->getComparator()->isRenamed()) {
                $name[] = 'rename_' . $table->getInitialName();
                continue;
            }

            $name[] = 'change_' . $table->getName();

            $comparator = $table->getComparator();

            foreach ($comparator->addedColumns() as $column) {
                $name[] = 'add_' . $column->getName();
            }

            foreach ($comparator->droppedColumns() as $column) {
                $name[] = 'rm_' . $column->getName();
            }

            foreach ($comparator->alteredColumns() as $column) {
                $name[] = 'alter_' . $column[0]->getName();
            }

            foreach ($comparator->addedIndexes() as $index) {
                $name[] = 'add_index_' . $index->getName();
            }

            foreach ($comparator->droppedIndexes() as $index) {
                $name[] = 'rm_index_' . $index->getName();
            }

            foreach ($comparator->alteredIndexes() as $index) {
                $name[] = 'alter_index_' . $index[0]->getName();
            }

            foreach ($comparator->addedForeignKeys() as $fk) {
                $name[] = 'add_fk_' . $fk->getName();
            }

            foreach ($comparator->droppedForeignKeys() as $fk) {
                $name[] = 'rm_fk_' . $fk->getName();
            }

            foreach ($comparator->alteredForeignKeys() as $fk) {
                $name[] = 'alter_fk_' . $fk[0]->getName();
            }
        }

        return \implode('_', $name);
    }
}
