<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ContactApiControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();
    }


    public function testCreateContactWithValidDataReturnsCreated(): void
    {
        $this->client->jsonRequest('POST', '/api/contact', [
            'contacterFirstName' => 'Jean',
            'contacterLastName'  => 'Dupont',
            'contacterEmail'     => 'jean.dupont@example.com',
            'type'               => 6,
            'description'        => 'Je souhaite signaler une erreur',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testCreateContactWithMissingRequiredFieldsReturnsValidationError(): void
    {
        $this->client->jsonRequest('POST', '/api/contact', [
            'contacterFirstName' => 'Jean',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCreateContactWithInvalidEmailReturnsValidationError(): void
    {
        $this->client->jsonRequest('POST', '/api/contact', [
            'contacterFirstName' => 'Jean',
            'contacterLastName'  => 'Dupont',
            'contacterEmail'     => 'not-an-email',
            'type'               => 6,
            'description'        => 'Test',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
