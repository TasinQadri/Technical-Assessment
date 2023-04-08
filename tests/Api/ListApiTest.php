<?php

namespace Yarikul\Tests\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ListApiTest extends BaseApiTestCase
{
    public function test_list_api(): void
    {
        $response = $this->request([
            'action' => 'list',
        ]);

        $first5Items = array_slice($response, 0, 5);

        self::assertSame(
            [
                [
                    'date' => '2020-05-25',
                    'supplier' => 'Jones-Jenkins',
                    'orderNumber' => 'S-1000001',
                    'product' => 'UCOME',
                    'pricePerTonne' => 688000,
                ],
                [
                    'date' => '2020-05-25',
                    'supplier' => 'Jones-Jenkins',
                    'orderNumber' => 'S-1000002',
                    'product' => 'UCOME',
                    'pricePerTonne' => 784000,
                ],
                [
                    'date' => '2020-05-25',
                    'supplier' => 'Jones-Jenkins',
                    'orderNumber' => 'S-1000003',
                    'product' => 'UCOME',
                    'pricePerTonne' => 976000,
                ],
                [
                    'date' => '2020-05-25',
                    'supplier' => 'Jones-Jenkins',
                    'orderNumber' => 'S-1000004',
                    'product' => 'UCOME',
                    'pricePerTonne' => 288000,
                ],
                [
                    'date' => '2020-05-25',
                    'supplier' => 'Jones-Jenkins',
                    'orderNumber' => 'S-1000005',
                    'product' => 'UCOME',
                    'pricePerTonne' => 368000,
                ],
            ],
            $first5Items,
        );
    }

    public function test_list_api_with_supplier_filter(): void
    {
        $response = $this->request([
            'action' => 'list',
            'supplier' => 'Balistreri-Kemmer',
        ]);

        $actual = $response[0];

        self::assertSame(
            [
                'date' => '2020-07-16',
                'supplier' => 'Balistreri-Kemmer',
                'orderNumber' => 'Ref-1002065',
                'product' => 'Used cooking oil (UCO)',
                'pricePerTonne' => 12288000,
            ],
            $actual,
        );
    }

    public function test_list_api_with_year_and_supplier_filter(): void
    {
        $response = $this->request([
            'action' => 'list',
            'year' => '2023',
            'supplier' => 'Jones-Jenkins',
        ]);

        $actual = $response[0];

        self::assertSame(
            [
                'date' => '2023-01-02',
                'supplier' => 'Jones-Jenkins',
                'orderNumber' => 'S-1009925',
                'product' => 'UCOME',
                'pricePerTonne' => 14592000,
            ],
            $actual,
        );
    }
}
