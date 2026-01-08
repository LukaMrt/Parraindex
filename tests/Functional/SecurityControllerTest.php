<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();
        self::getContainer()->get(PersonRepository::class);
        self::getContainer()->get(UserRepository::class);
    }

    public function testLoginPageIsAccessible(): void
    {
        // When
        $this->client->request(Request::METHOD_GET, '/login');

        // Then
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
        $this->assertSelectorExists('input[name="_csrf_token"]');
    }

    public function testLogoutSuccessPageIsAccessible(): void
    {
        // When
        $this->client->request(Request::METHOD_GET, '/logout/success');

        // Then
        $this->assertResponseIsSuccessful();
    }

    public function testRegisterPageIsAccessible(): void
    {
        // When
        $this->client->request(Request::METHOD_GET, '/register');

        // Then
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="registration_form[email]"]');
        $this->assertSelectorExists('input[name="registration_form[password][first]"]');
        $this->assertSelectorExists('input[name="registration_form[password][second]"]');
    }


    public function testRegisterSuccessPageIsAccessible(): void
    {
        // When
        $this->client->request(Request::METHOD_GET, '/register/success');

        // Then
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.signup_confirm', 'Votre inscription a été enregistrée');
    }

    public function testVerifyEmailWithoutId(): void
    {
        // When
        $this->client->request(Request::METHOD_GET, '/register/verify');

        // Then
        $this->assertResponseRedirects('/register');
    }

    public function testVerifyEmailWithInvalidId(): void
    {
        // When
        $this->client->request(Request::METHOD_GET, '/register/verify?id=999999');

        // Then
        $this->assertResponseRedirects('/register');
    }
}
