<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Violet\StreamingJsonEncoder\BufferJsonEncoder;
use Violet\StreamingJsonEncoder\JsonStream;
use Yarikul\Filters;
use Yarikul\PurchaseOrdersService;
use Yarikul\SpreadSheetIntoDatabaseImporter;

require_once dirname(__DIR__).'/vendor/autoload.php';

ini_set('display_errors', 1);

$db = dirname(__DIR__).'/db.sqlite';

$dsnParser = new DsnParser(schemeMapping: ['sqlite' => 'pdo_sqlite']);
$connection = DriverManager::getConnection($dsnParser->parse("sqlite:///$db"));

// import data from CSV file into database if it doesn't exist
if (!is_file($db)) {
    $spreadSheetIntoDatabaseImporter = new SpreadSheetIntoDatabaseImporter($connection);
    $spreadSheetIntoDatabaseImporter->import(
        file: dirname(__DIR__).'/purchase-orders.csv',
        tableName: 'purchase_orders',
    );
}

$purchaseOrders = new PurchaseOrdersService($connection);

$filters = new Filters(
    $_GET['supplier'] ?? null,
    $_GET['product'] ?? null,
    $_GET['year'] ?? null,
);

$_GET['action'] = $_GET['action'] ?? 'list';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    $stream = new JsonStream(
        new BufferJsonEncoder(
            match ($_GET['action']) {
                'list' => $purchaseOrders->list($filters),
                'average-product-price-per-year' => $purchaseOrders->averageProductPricePerTonnePerYear($filters),
                default => throw new \InvalidArgumentException('Invalid action'),
            }
        )
    );

    while (!$stream->eof()) {
        echo $stream->read(1024 * 8);
    }
} catch (\Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTrace(),
    ]);
}
