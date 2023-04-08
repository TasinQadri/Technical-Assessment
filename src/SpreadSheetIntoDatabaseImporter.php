<?php

declare(strict_types=1);

namespace Yarikul;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Reader\Common\Creator\ReaderFactory;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use OpenSpout\Reader\ReaderInterface;

class SpreadSheetIntoDatabaseImporter
{
    private const COLUMN_DEFINITIONS = [
        'date' => Types::DATE_IMMUTABLE,
        'supplier' => Types::STRING,
        'order_number' => Types::STRING,
        'product' => Types::STRING,
        'price_per_100_grams' => Types::DECIMAL,
        'quantity_in_kg' => Types::DECIMAL,
    ];

    private readonly ReaderInterface $reader;

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @throws IOException
     * @throws ReaderNotOpenedException
     * @throws SchemaException
     * @throws Exception
     * @throws UnsupportedTypeException
     */
    public function import(string $file, string $tableName): void
    {
        $this->reader = ReaderFactory::createFromFile($file);

        $this->createTable($tableName, $file);
        $this->loadSpreadSheetIntoTable($tableName, $file);
    }

    /**
     * @throws IOException
     * @throws ReaderNotOpenedException
     * @throws SchemaException
     * @throws Exception
     */
    private function createTable(string $tableName, string $file): void
    {
        $this->reader->open($file);

        $sheet = $this->reader->getSheetIterator()->current();

        $sheet->getRowIterator()->rewind();
        $row = $sheet->getRowIterator()->current();

        $columns = [];
        foreach ($row->getCells() as $cell) {
            $header = self::convertToSnakeCase($cell->getValue());

            $type = self::COLUMN_DEFINITIONS[$header]
                ?? throw new \InvalidArgumentException("Unknown column '$header'");

            $columns[] = new Column($header, Type::getType($type));
        }

        $this->reader->close();

        $schemaManager = $this->connection->createSchemaManager();

        $table = new Table($tableName, $columns);

        try {
            $schemaManager->dropTable($tableName);
        } catch (TableNotFoundException) {
        }

        $schemaManager->createTable($table);
    }

    /**
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    private function loadSpreadSheetIntoTable(string $tableName, string $file): void
    {
        $this->reader->open($file);
        $sheet = $this->reader->getSheetIterator()->current();

        // wrap in transaction to avoid partial inserts
        $this->connection->transactional(function (Connection $connection) use ($sheet, $tableName) {
            $headerSkipped = false;

            foreach ($sheet->getRowIterator() as $row) {
                if (!$headerSkipped) {
                    $headerSkipped = true;
                    continue;
                }

                $this->loadRowIntoTable($tableName, $row);
            }
        });
    }

    /**
     * @throws Exception
     */
    private function loadRowIntoTable(string $tableName, Row $row): void
    {
        $values = [];
        foreach ($row->getCells() as $cell) {
            $values[] = $cell->getValue();
        }

        $columns = \array_keys(self::COLUMN_DEFINITIONS);

        $this->connection->insert($tableName, \array_combine($columns, $values));
    }

    private static function convertToSnakeCase(string $header): string
    {
        return \strtolower(\str_replace(' ', '_', $header));
    }
}
