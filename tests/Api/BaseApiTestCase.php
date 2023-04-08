<?php

declare(strict_types=1);

namespace Yarikul\Tests\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class BaseApiTestCase extends TestCase
{
    private HttpClientInterface $client;

    protected function setUp(): void
    {
        $this->client = HttpClient::create();
    }

    final protected function request(array $query = []): array
    {
        return $this->client
            ->request('GET', $_SERVER['API_URL'], ['query' => $query])
            ->toArray();
    }
}