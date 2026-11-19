<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Traits\ApiTestTrait;
use App\Entity\Contact\Contact;
use App\Entity\Contact\Type as ContactType;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AdminApiControllerTest extends WebTestCase
{
    use ApiTestTrait;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testListContactsAsAdminReturnsContacts(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');

        $this->client->request(Request::METHOD_GET, '/api/admin/contacts');

        self::assertResponseIsSuccessful();
        $items = $this->responseDataList();
        self::assertGreaterThanOrEqual(0, count($items));
    }

    public function testListContactsAsNonAdminReturnsForbidden(): void
    {
        $this->loginAs('lilian.baudry@etu.univ-lyon1.fr');

        $this->client->request(Request::METHOD_GET, '/api/admin/contacts');

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListContactsRequiresAuthentication(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/admin/contacts');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testResolveContactAsAdminReturnsSuccess(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');
        $contact = $this->createPendingContact();

        $this->client->jsonRequest('PUT', '/api/admin/contacts/' . $this->idOf($contact));

        self::assertResponseIsSuccessful();
    }

    public function testResolveAlreadyResolvedContactReturnsError(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');
        $contact = $this->createPendingContact();

        $this->client->jsonRequest('PUT', '/api/admin/contacts/' . $this->idOf($contact));
        self::assertResponseIsSuccessful();

        $this->client->jsonRequest('PUT', '/api/admin/contacts/' . $this->idOf($contact));
        self::assertResponseIsUnprocessable();
    }

    private function createPendingContact(): Contact
    {
        $contact = new Contact();
        $contact->setContacterFirstName('Test')
            ->setContacterLastName('Contact')
            ->setContacterEmail('test@example.com')
            ->setType(ContactType::OTHER)
            ->setDescription('Test contact')
            ->setCreatedAt(new \DateTime());

        /** @var ContactRepository $contactRepository */
        $contactRepository = self::getContainer()->get(ContactRepository::class);
        $contactRepository->create($contact);

        return $contact;
    }
}
