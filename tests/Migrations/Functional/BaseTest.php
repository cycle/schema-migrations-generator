<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Functional;

use Cycle\Annotated\Entities;
use Cycle\Annotated\Locator\TokenizerEntityLocator;
use Cycle\Annotated\MergeColumns;
use Cycle\Annotated\MergeIndexes;
use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Database;
use Cycle\Database\DatabaseManager;
use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\Driver\Handler;
use Cycle\Database\Schema\AbstractTable;
use Cycle\Database\Schema\Comparator;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\FileRepository;
use Cycle\Migrations\Migrator;
use Cycle\ORM\Config\RelationConfig;
use Cycle\ORM\Factory;
use Cycle\ORM\ORM;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Compiler;
use Cycle\Schema\Generator\GenerateRelations;
use Cycle\Schema\Generator\GenerateTypecast;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Cycle\Schema\Generator\Migrations\Tests\TestLogger;
use Cycle\Schema\Generator\RenderRelations;
use Cycle\Schema\Generator\RenderTables;
use Cycle\Schema\Generator\ResetTables;
use Cycle\Schema\Generator\ValidateEntities;
use Cycle\Schema\Registry;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Spiral\Attributes\AttributeReader;
use Spiral\Core\Container;
use Spiral\Files\Files;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;

abstract class BaseTest
{
    // currently active driver
    public const DRIVER = null;
    public const CONFIG = [
        'directory' => __DIR__ . '/../../files/',
        'table' => 'migrations',
        'safe' => true,
        'namespace' => 'Migration',
    ];

    // tests configuration
    public static array $config;

    // cross test driver cache
    public static array $driverCache = [];
    protected DriverInterface $driver;
    protected ?DatabaseManager $dbal = null;
    protected ?ORM $orm = null;
    protected LoggerInterface $logger;
    protected ClassesInterface $locator;
    protected Migrator $migrator;

    /**
     * Calculates missing parameters for typecasting.
     */
    public function withSchema(SchemaInterface $schema): ORM
    {
        $this->orm = $this->orm->withSchema($schema);
        return $this->orm;
    }

    public function getDriver(): DriverInterface
    {
        if (isset(static::$driverCache[static::DRIVER])) {
            return static::$driverCache[static::DRIVER];
        }

        $config = self::$config[static::DRIVER];
        if (!isset($this->driver)) {
            $this->driver = $config->driver::create($config);
        }

        return static::$driverCache[static::DRIVER] = $this->driver;
    }

    /**
     * Init all we need.
     */
    #[\Testo\Lifecycle\BeforeTest]
    public function setUp(): void
    {
        $this->dbal = new DatabaseManager(new DatabaseConfig([
            'default' => 'default',
            'databases' => [],
        ]));
        $this->dbal->addDatabase(new Database(
            'default',
            '',
            $this->getDriver(),
        ));

        $this->dbal->addDatabase(new Database(
            'secondary',
            'secondary_',
            $this->getDriver(),
        ));

        $this->logger = new TestLogger();
        $this->getDriver()->setLogger($this->logger);

        if (self::$config['debug']) {
            $this->logger->display();
        }

        $this->logger = new TestLogger();
        $this->getDriver()->setLogger($this->logger);

        if (self::$config['debug']) {
            $this->logger->display();
        }

        $this->orm = new ORM(new Factory(
            $this->dbal,
            RelationConfig::getDefault(),
        ), new \Cycle\ORM\Schema([]));

        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/Fixtures'],
            'exclude' => [],
        ]));

        $this->locator = $tokenizer->classLocator();

        $config = new MigrationConfig(static::CONFIG);

        $this->migrator = new Migrator(
            $config,
            $this->dbal,
            new FileRepository(
                $config,
                new Container(),
            ),
        );

        $this->migrator->configure();
    }

    /**
     * Cleanup.
     */
    #[\Testo\Lifecycle\AfterTest]
    public function tearDown(): void
    {
        $files = new Files();
        foreach ($files->getFiles(\dirname(__DIR__, 2) . '/files', '*.php') as $file) {
            $files->delete($file);
            clearstatcache(true, $file);
        }

        $this->disableProfiling();
        $this->dropDatabase($this->dbal->database('default'));
        $this->orm = null;
        $this->dbal = null;
    }

    protected function migrate(string $directory): array
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [$directory],
            'exclude' => [],
        ]));

        $locator = $tokenizer->classLocator();

        $reader = new AttributeReader();
        $r = new Registry($this->dbal);

        (new Compiler())->compile($r, [
            new Entities(new TokenizerEntityLocator($locator, $reader), $reader),
            new ResetTables(),
            new MergeColumns($reader),
            new GenerateRelations(),
            new ValidateEntities(),
            new RenderTables(),
            new RenderRelations(),
            new MergeIndexes($reader),
            new GenerateTypecast(),
            $this->getGenerateMigrations(),
        ]);

        $tables = [];
        foreach ($r as $e) {
            $tables[] = $r->getTableSchema($e);
        }
        return $tables;
    }

    protected function getDatabase(): Database
    {
        return $this->dbal->database('default');
    }

    protected function dropDatabase(?Database $database = null): void
    {
        if (empty($database)) {
            return;
        }

        foreach ($database->getTables() as $table) {
            $schema = $table->getSchema();

            foreach ($schema->getForeignKeys() as $foreign) {
                $schema->dropForeignKey($foreign->getColumns());
            }

            $schema->save(Handler::DROP_FOREIGN_KEYS);
        }

        foreach ($database->getTables() as $table) {
            $schema = $table->getSchema();
            $schema->declareDropped();
            $schema->save();
        }
    }

    /**
     * For debug purposes only.
     */
    protected function enableProfiling(): void
    {
        if ($this->logger !== null) {
            $this->logger->display();
        }
    }

    /**
     * For debug purposes only.
     */
    protected function disableProfiling(): void
    {
        if ($this->logger !== null) {
            $this->logger->hide();
        }
    }

    #[\Testo\Common\Attribute\AssertMethod]
    protected function assertSameAsInDB(AbstractTable $current): void
    {
        $source = $current->getState();
        $target = $current->getDriver()->getSchemaHandler()->getSchema($current->getName())->getState();

        // testing changes

        \Testo\Assert::same($target->getPrimaryKeys(), $source->getPrimaryKeys(), 'Primary keys changed');

        \Testo\Assert::same(count($target->getColumns()), count($source->getColumns()), 'Column number has changed');

        \Testo\Assert::same(count($target->getIndexes()), count($source->getIndexes()), 'Index number has changed');

        \Testo\Assert::same(count($target->getForeignKeys()), count($source->getForeignKeys()), 'FK number has changed');

        // columns

        foreach ($source->getColumns() as $column) {
            \Testo\Assert::true($target->hasColumn($column->getName()), "Column {$column} has been removed");

            \Testo\Assert::true($column->compare($target->findColumn($column->getName())), "Column {$column} has been changed");
        }

        foreach ($target->getColumns() as $column) {
            \Testo\Assert::true($source->hasColumn($column->getName()), "Column {$column} has been added");

            \Testo\Assert::true($column->compare($source->findColumn($column->getName())), "Column {$column} has been changed");
        }

        // indexes

        foreach ($source->getIndexes() as $index) {
            \Testo\Assert::true($target->hasIndex($index->getColumns()), "Index {$index->getName()} has been removed");

            \Testo\Assert::true($index->compare($target->findIndex($index->getColumns())), "Index {$index->getName()} has been changed");
        }

        foreach ($target->getIndexes() as $index) {
            \Testo\Assert::true($source->hasIndex($index->getColumns()), "Index {$index->getName()} has been removed");

            \Testo\Assert::true($index->compare($source->findIndex($index->getColumns())), "Index {$index->getName()} has been changed");
        }

        // FK
        foreach ($source->getForeignKeys() as $key) {
            \Testo\Assert::true($target->hasForeignKey($key->getColumns()), "FK {$key->getName()} has been removed");

            \Testo\Assert::true($key->compare($target->findForeignKey($key->getColumns())), "FK {$key->getName()} has been changed");
        }

        foreach ($target->getForeignKeys() as $key) {
            \Testo\Assert::true($source->hasForeignKey($key->getColumns()), "FK {$key->getName()} has been removed");

            \Testo\Assert::true($key->compare($source->findForeignKey($key->getColumns())), "FK {$key->getName()} has been changed");
        }

        // everything else
        $comparator = new Comparator(
            $current->getState(),
            $current->getDriver()->getSchemaHandler()->getSchema($current->getName())->getState(),
        );

        if ($comparator->hasChanges()) {
            \Testo\Assert::fail($this->makeMessage($current->getName(), $comparator));
        }
    }

    protected function makeMessage(string $table, Comparator $comparator): string
    {
        if ($comparator->isPrimaryChanged()) {
            return "Table '{$table}' not synced, primary indexes are different.";
        }

        if ($comparator->droppedColumns()) {
            return "Table '{$table}' not synced, columns are missing.";
        }

        if ($comparator->addedColumns()) {
            return "Table '{$table}' not synced, new columns found.";
        }

        if ($comparator->alteredColumns()) {
            $names = [];
            foreach ($comparator->alteredColumns() as $pair) {
                $names[] = $pair[0]->getName();
                print_r($pair);
            }

            return "Table '{$table}' not synced, column(s) '" . implode(
                "', '",
                $names,
            ) . "' have been changed.";
        }

        if ($comparator->droppedForeignKeys()) {
            return "Table '{$table}' not synced, FKs are missing.";
        }

        if ($comparator->addedForeignKeys()) {
            return "Table '{$table}' not synced, new FKs found.";
        }


        return "Table '{$table}' not synced, no idea why, add more messages :P";
    }

    protected function getGenerateMigrations(): GenerateMigrations
    {
        return new GenerateMigrations($this->migrator->getRepository(), new MigrationConfig(static::CONFIG));
    }
}
