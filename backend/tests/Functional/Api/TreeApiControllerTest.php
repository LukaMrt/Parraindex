<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Traits\ApiTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class TreeApiControllerTest extends WebTestCase
{
    use ApiTestTrait;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testTreeReturnsAllPersonsAsSummaries(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/tree');

        self::assertResponseIsSuccessful();
        $items = $this->responseItems();
        self::assertNotEmpty($items);

        $first = $items[0];
        self::assertArrayHasKey('id', $first);
        self::assertArrayHasKey('firstName', $first);
        self::assertArrayHasKey('lastName', $first);
        self::assertArrayHasKey('startYear', $first);
        self::assertArrayHasKey('picture', $first);
    }

    public function testTreeIsAccessibleWithoutAuthentication(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/tree');

        self::assertResponseIsSuccessful();
    }

    public function testTreeReturnsPersonsOrderedById(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/tree');

        self::assertResponseIsSuccessful();
        $items = $this->responseItems();

        $ids       = array_column($items, 'id');
        $sortedIds = $ids;
        sort($sortedIds);
        self::assertSame($sortedIds, $ids);
    }

    public function testTreeReturnsTotalCount(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/tree');

        self::assertResponseIsSuccessful();
        $data = $this->responseData();
        self::assertArrayHasKey('total', $data);
        self::assertGreaterThan(0, $data['total']);
    }

    public function testTreePaginationLimitsResults(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/tree?page=1&limit=2');

        self::assertResponseIsSuccessful();
        $items = $this->responseItems();
        self::assertCount(2, $items);
    }

    public function testTreeNeverExposesPrivateCharacteristics(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/tree?page=1&limit=50');

        self::assertResponseIsSuccessful();
        foreach ($this->responseItems() as $person) {
            self::assertIsArray($person['characteristics']);
            foreach ($person['characteristics'] as $characteristic) {
                self::assertIsArray($characteristic);
                self::assertTrue($characteristic['visible']);
            }
        }
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
}
