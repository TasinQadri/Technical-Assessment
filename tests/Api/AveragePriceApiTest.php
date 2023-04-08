<?php

namespace Yarikul\Tests\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AveragePriceApiTest extends BaseApiTestCase
{
    public function test_average_product_price_per_year_api(): void
    {
        $response = $this->request([
            'action' => 'average-product-price-per-year',
        ]);

        $firstItem = $response[0];

        self::assertSame('KM32', $firstItem['product']);
        self::assertSame(4881803.271, $firstItem['pricePerTonne']);
        self::assertSame('Hahn, Mueller and Strosin', $firstItem['supplier']);

        $secondItem = $response[1];
        self::assertSame('Methanol', $secondItem['product']);
        self::assertSame(3862897.815, $secondItem['pricePerTonne']);
        self::assertSame('Hahn, Mueller and Strosin', $secondItem['supplier']);
    }

    public function test_average_product_price_per_year_api_with_supplier_filter(): void
    {
        $response = $this->request([
            'action' => 'average-product-price-per-year',
            'supplier' => 'Treutel, Pouros and Murphy',
        ]);

        $actual = $response[0];

        self::assertSame('Sunflower oil', $actual['product']);
        self::assertSame(4791811.129, $actual['pricePerTonne']);
        self::assertSame('Treutel, Pouros and Murphy', $actual['supplier']);
    }
}
