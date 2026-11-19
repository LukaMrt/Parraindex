<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Dto\Sponsor\SponsorTypeDto;
use App\Entity\Contact\Contact;
use App\Entity\Sponsor\Type as SponsorType;
use App\Repository\ContactRepository;
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

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testCreateContactWithSponsorTypeStoresIt(): void
    {
        $this->client->jsonRequest('POST', '/api/contact', [
            'contacterFirstName' => 'Jean',
            'contacterLastName'  => 'Dupont',
            'contacterEmail'     => 'jean.dupont@example.com',
            'type'               => 6,
            'description'        => "Ajout d'un parrainage",
            'sponsorType'        => SponsorTypeDto::FALUCHE->value,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        /** @var ContactRepository $contactRepository */
        $contactRepository = self::getContainer()->get(ContactRepository::class);
        $contact = $contactRepository->findOneBy([], ['id' => 'DESC']);
        self::assertInstanceOf(Contact::class, $contact);
        self::assertSame(SponsorType::FALUCHE, $contact->getSponsorType());
    }

    public function testCreateContactWithUnknownSponsorTypeReturnsValidationError(): void
    {
        $this->client->jsonRequest('POST', '/api/contact', [
            'contacterFirstName' => 'Jean',
            'contacterLastName'  => 'Dupont',
            'contacterEmail'     => 'jean.dupont@example.com',
            'type'               => 6,
            'description'        => "Ajout d'un parrainage",
            'sponsorType'        => 'INVALID',
        ]);

        self::assertResponseIsUnprocessable();
    }

    public function testCreateContactWithMissingRequiredFieldsReturnsValidationError(): void
    {
        $this->client->jsonRequest('POST', '/api/contact', [
            'contacterFirstName' => 'Jean',
        ]);

        self::assertResponseIsUnprocessable();
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

        self::assertResponseIsUnprocessable();
    }
}
