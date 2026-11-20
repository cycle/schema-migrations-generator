<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Feature\Common;

use Cycle\Database\Schema\AbstractTable;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Schema\Generator\Migrations\NameBasedOnChangesGenerator;
use Cycle\Schema\Generator\Migrations\Strategy\GeneratorStrategyInterface;
use Cycle\Schema\Generator\Migrations\Strategy\MultipleFilesStrategy;
use Cycle\Schema\Generator\Migrations\Strategy\SingleFileStrategy;
use Cycle\Testing\Database\DatabaseTestCase;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Test;

#[Test]
final class StrategyTest extends DatabaseTestCase
{
    public static function strategies(): iterable
    {
        $config = new MigrationConfig(['namespace' => 'Migration']);

        yield 'single file' => [new SingleFileStrategy($config, new NameBasedOnChangesGenerator())];
        yield 'multiple files' => [new MultipleFilesStrategy($config, new NameBasedOnChangesGenerator())];
    }

    #[DataProvider('strategies')]
    public function namesTheMigrationAfterTheChanges(GeneratorStrategyInterface $strategy): void
    {
        $images = $strategy->generate('default', [$this->newUsersTable()]);

        Assert::count($images, 1);
        Assert::same($images[0]->getName(), 'create_users');
        Assert::string($images[0]->buildFileName())->matchesRegex('/^\d+_default_create_users$/');
    }

    #[DataProvider('strategies')]
    public function numbersTheMigrationFilesInAscendingOrder(GeneratorStrategyInterface $strategy): void
    {
        $first = $strategy->generate('default', [$this->newUsersTable()])[0]->buildFileName();
        $second = $strategy->generate('default', [$this->newUsersTable()])[0]->buildFileName();

        Assert::same((int) $second, (int) $first + 1);
    }

    #[DataProvider('strategies')]
    public function declaresAndRevertsTheChanges(GeneratorStrategyInterface $strategy): void
    {
        $class = $strategy->generate('default', [$this->newUsersTable()])[0]->getClass();

        Assert::string($class->getMethod('up')->getBody())->contains("table('users')")->contains('->create()');
        Assert::string($class->getMethod('down')->getBody())->contains("table('users')")->contains('->drop()');
    }

    #[DataProvider('strategies')]
    public function skipsTablesWithoutChanges(GeneratorStrategyInterface $strategy): void
    {
        $users = $this->newUsersTable();
        $users->save();

        Assert::same($strategy->generate('default', [$this->database->table('users')->getSchema()]), []);
    }

    private function newUsersTable(): AbstractTable
    {
        $table = $this->database->table('users')->getSchema();
        $table->primary('id');
        $table->string('email');

        return $table;
    }
}
