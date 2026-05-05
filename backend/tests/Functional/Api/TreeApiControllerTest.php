<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class TreeApiControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();
    }


    public function testTreeReturnsAllPersonsAsSummaries(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/tree');

        $this->assertResponseIsSuccessful();
        $items = $this->responseItems();
        $this->assertNotEmpty($items);

        $first = $items[0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('firstName', $first);
        $this->assertArrayHasKey('lastName', $first);
        $this->assertArrayHasKey('startYear', $first);
        $this->assertArrayHasKey('picture', $first);
        $this->assertArrayHasKey('color', $first);
    }

    public function testTreeIsAccessibleWithoutAuthentication(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/tree');

        $this->assertResponseIsSuccessful();
    }

    public function testTreeReturnsPersonsOrderedById(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/tree');

        $this->assertResponseIsSuccessful();
        $items = $this->responseItems();

        $ids       = array_column($items, 'id');
        $sortedIds = $ids;
        sort($sortedIds);
        $this->assertSame($sortedIds, $ids);
    }

    public function testTreeReturnsTotalCount(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/tree');

        $this->assertResponseIsSuccessful();
        $data = $this->responseData();
        $this->assertArrayHasKey('total', $data);
        $this->assertGreaterThan(0, $data['total']);
    }

    public function testTreePaginationLimitsResults(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/tree?page=1&limit=2');

        $this->assertResponseIsSuccessful();
        $items = $this->responseItems();
        $this->assertCount(2, $items);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function responseItems(): array
    {
        /** @var array<int, array<string, mixed>> $items */
        $items = $this->responseData()['items'] ?? [];
        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function responseData(): array
    {
        /** @var array<string, array<string, mixed>> $body */
        $body = json_decode((string) $this->client->getResponse()->getContent(), true);
        return $body['data'] ?? [];
    }
}
