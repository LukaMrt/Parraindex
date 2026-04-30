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
        $items = $this->responseDataList();
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
        $items = $this->responseDataList();

        $ids       = array_column($items, 'id');
        $sortedIds = $ids;
        sort($sortedIds);
        $this->assertSame($sortedIds, $ids);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function responseDataList(): array
    {
        /** @var array<string, array<int, array<string, mixed>>> $body */
        $body = json_decode((string) $this->client->getResponse()->getContent(), true);
        return $body['data'] ?? [];
    }
}
