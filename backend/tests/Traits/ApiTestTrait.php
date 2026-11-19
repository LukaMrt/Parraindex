<?php

declare(strict_types=1);

namespace App\Tests\Traits;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Person\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Helpers partagés par les tests fonctionnels de l'API JSON.
 *
 * @phpstan-require-extends WebTestCase
 */
trait ApiTestTrait
{
    private KernelBrowser $client;

    /**
     * Les firewalls `admin` et `api` partagent `context: main` : le token doit
     * être stocké sous `_security_main`, d'où le contexte par défaut de loginUser().
     */
    private function loginAs(string $email): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => $email]);
        $this->assertInstanceOf(User::class, $user);
        $this->client->loginUser($user);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function responseData(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->responseBody()['data'] ?? [];

        return $data;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function responseDataList(): array
    {
        /** @var list<array<string, mixed>> $data */
        $data = $this->responseBody()['data'] ?? [];

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function responseError(): array
    {
        /** @var array<string, mixed> $error */
        $error = $this->responseBody()['error'] ?? [];

        return $error;
    }

    /**
     * @return array<string, mixed>
     */
    private function responseBody(): array
    {
        $body = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($body);

        /** @var array<string, mixed> $body */
        return $body;
    }
}
