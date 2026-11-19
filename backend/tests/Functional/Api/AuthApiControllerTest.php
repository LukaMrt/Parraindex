<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Traits\ApiTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthApiControllerTest extends WebTestCase
{
    use ApiTestTrait;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testLoginWithValidCredentialsReturnsUserData(): void
    {
        $this->client->jsonRequest('POST', '/api/auth/login', [
            'email'    => 'luka.maret@etu.univ-lyon1.fr',
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
            'email'    => 'luka.maret@etu.univ-lyon1.fr',
            'password' => 'wrong_password',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $error = $this->responseError();
        $this->assertArrayHasKey('code', $error);
    }

    public function testLoginWithUnknownEmailReturnsUnauthorized(): void
    {
        $this->client->jsonRequest('POST', '/api/auth/login', [
            'email'    => 'unknown@etu.univ-lyon1.fr',
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

        $this->assertResponseIsUnprocessable();
        $error = $this->responseError();
        $this->assertSame('VALIDATION_ERROR', $error['code']);
    }

    public function testResetPasswordRequestAlwaysReturnsSuccess(): void
    {
        $this->client->jsonRequest('POST', '/api/auth/reset-password/request', [
            'email'       => 'unknown@etu.univ-lyon1.fr',
            'callbackUrl' => 'http://localhost/reset-password',
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testResetPasswordRequestWithoutCallbackUrlReturnsValidationError(): void
    {
        $this->client->jsonRequest('POST', '/api/auth/reset-password/request', [
            'email' => 'unknown@etu.univ-lyon1.fr',
        ]);

        $this->assertResponseIsUnprocessable();
        $this->assertSame('VALIDATION_ERROR', $this->responseError()['code']);
    }

    public function testResetPasswordConfirmWithInvalidTokenReturnsError(): void
    {
        $this->client->jsonRequest('POST', '/api/auth/reset-password/confirm', [
            'token'    => 'invalid_token',
            'password' => 'NewStr0ng!Pass',
        ]);

        $this->assertResponseIsUnprocessable();
        $error = $this->responseError();
        $this->assertSame('VALIDATION_ERROR', $error['code']);
    }
}
