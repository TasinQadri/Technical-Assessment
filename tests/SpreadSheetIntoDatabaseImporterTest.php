<?php

declare(strict_types=1);

namespace Yarikul\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\TestCase;
use Yarikul\SpreadSheetIntoDatabaseImporter;

class SpreadSheetIntoDatabaseImporterTest extends TestCase
{
    private readonly SpreadSheetIntoDatabaseImporter $spreadSheetIntoDatabaseImporter;

    private readonly Connection $connection;

    protected function setUp(): void
    {
        $dsnParser = new DsnParser(schemeMapping: ['sqlite' => 'pdo_sqlite']);

        $this->connection = DriverManager::getConnection($dsnParser->parse('sqlite:///:memory:'));

        $this->spreadSheetIntoDatabaseImporter = new SpreadSheetIntoDatabaseImporter(
            connection: $this->connection,
        );
    }

    public function test_it_creates_purchase_orders_table(): void
    {
        $this->spreadSheetIntoDatabaseImporter->import(
            file: \dirname(__DIR__).'/purchase-orders.csv',
            tableName: 'purchase_orders',
        );

        $actual = $this->connection->fetchOne('SELECT 1 FROM purchase_orders');
        $expected = 1;

        self::assertSame(
            $expected,
            $actual,
            'Expected to have table purchase_orders with at least one row',
        );
    }

    public function test_purchase_orders_table_has_columns_as_per_definition(): void
    {
        $this->spreadSheetIntoDatabaseImporter->import(
            file: \dirname(__DIR__).'/purchase-orders.csv',
            tableName: 'purchase_orders',
        );

        $schemaManager = $this->connection->createSchemaManager();

        $table = $schemaManager->introspectTable('purchase_orders');

        $columns = [
            'date' => Types::DATE_IMMUTABLE,
            'supplier' => Types::STRING,
            'order_number' => Types::STRING,
            'product' => Types::STRING,
            'price_per_100_grams' => Types::DECIMAL,
            'quantity_in_kg' => Types::DECIMAL,
        ];

        foreach ($columns as $columnName => $columnType) {
            self::assertTrue(
                $table->hasColumn($columnName),
                "Expected to have column $columnName",
            );

            self::assertSame(
                $columnType,
                $table->getColumn($columnName)->getType()->getName(),
                "Expected to have column $columnName of type $columnType",
            );
        }
    }

    public function test_it_imports_data_into_database(): void
    {
        $this->spreadSheetIntoDatabaseImporter->import(
            file: \dirname(__DIR__).'/purchase-orders.csv',
            tableName: 'purchase_orders',
        );

        $actual = $this->connection->fetchOne('SELECT COUNT(*) FROM purchase_orders');
        $expected = 0;

        self::assertGreaterThan(
            $expected,
            $actual,
            'Expected to have more than 0 rows in the database',
        );
    }
}
