<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Person\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthApiControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();
    }


    public function testLoginWithValidCredentialsReturnsUserData(): void
    {
        $this->client->jsonRequest('POST', '/api/auth/login', [
            'username' => 'luka.maret@etu.univ-lyon1.fr',
            'password' => 'password',
        ]);

        $this->assertResponseIsSuccessful();
        $data = $this->responseData();
        $this->assertArrayHasKey('email', $data);
        $this->assertSame('luka.maret@etu.univ-lyon1.fr', $data['email']);
        $this->assertArrayHasKey('isAdmin', $data);
        $this->assertTrue((bool) $data['isAdmin']);
    }

    public function testLoginWithInvalidCredentialsReturnsUnauthorized(): void
    {
        $this->client->jsonRequest('POST', '/api/auth/login', [
            'username' => 'luka.maret@etu.univ-lyon1.fr',
            'password' => 'wrong_password',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $error = $this->responseError();
        $this->assertArrayHasKey('code', $error);
    }

    public function testLoginWithUnknownEmailReturnsUnauthorized(): void
    {
        $this->client->jsonRequest('POST', '/api/auth/login', [
            'username' => 'unknown@etu.univ-lyon1.fr',
            'password' => 'password',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }


    public function testMeWhenAuthenticatedReturnsUserProfile(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');

        $this->client->request(Request::METHOD_GET, '/api/auth/me');

        $this->assertResponseIsSuccessful();
        $data = $this->responseData();
        $this->assertSame('luka.maret@etu.univ-lyon1.fr', $data['email']);
        $this->assertArrayHasKey('person', $data);
    }

    public function testMeWhenNotAuthenticatedReturnsUnauthorized(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/auth/me');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $error = $this->responseError();
        $this->assertSame('UNAUTHORIZED', $error['code']);
    }


    public function testLogoutWhenAuthenticatedReturnsSuccess(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');

        $this->client->jsonRequest('POST', '/api/auth/logout');

        $this->assertResponseIsSuccessful();
    }


    public function testRegisterWithMissingFieldsReturnsValidationError(): void
    {
        $this->client->jsonRequest('POST', '/api/auth/register', [
            'email' => 'test@etu.univ-lyon1.fr',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $error = $this->responseError();
        $this->assertSame('VALIDATION_ERROR', $error['code']);
    }


    public function testResetPasswordRequestAlwaysReturnsSuccess(): void
    {
        $this->client->jsonRequest('POST', '/api/auth/reset-password/request', [
            'email' => 'unknown@etu.univ-lyon1.fr',
        ]);

        $this->assertResponseIsSuccessful();
    }


    public function testResetPasswordConfirmWithInvalidTokenReturnsError(): void
    {
        $this->client->jsonRequest('POST', '/api/auth/reset-password/confirm', [
            'token'    => 'invalid_token',
            'password' => 'NewStr0ng!Pass',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $error = $this->responseError();
        $this->assertSame('VALIDATION_ERROR', $error['code']);
    }


    private function loginAs(string $email): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => $email]);
        $this->assertInstanceOf(User::class, $user);
        $this->client->loginUser($user, 'api');
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

    /**
     * @return array<string, mixed>
     */
    private function responseError(): array
    {
        /** @var array<string, array<string, mixed>> $body */
        $body = json_decode((string) $this->client->getResponse()->getContent(), true);
        return $body['error'] ?? [];
    }
}
