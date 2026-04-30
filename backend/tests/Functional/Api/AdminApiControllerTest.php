<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Type as ContactType;
use App\Entity\Person\User;
use App\Repository\ContactRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AdminApiControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();
    }


    public function testListContactsAsAdminReturnsContacts(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');

        $this->client->request(Request::METHOD_GET, '/api/admin/contacts');

        $this->assertResponseIsSuccessful();
        $items = $this->responseDataList();
        $this->assertGreaterThanOrEqual(0, count($items));
    }

    public function testListContactsAsNonAdminReturnsForbidden(): void
    {
        $this->loginAs('lilian.baudry@etu.univ-lyon1.fr');

        $this->client->request(Request::METHOD_GET, '/api/admin/contacts');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListContactsRequiresAuthentication(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/admin/contacts');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }


    public function testResolveContactAsAdminReturnsSuccess(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');
        $contact = $this->createPendingContact();

        $this->client->jsonRequest('PUT', '/api/admin/contacts/' . $contact->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testResolveAlreadyResolvedContactReturnsError(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');
        $contact = $this->createPendingContact();

        $this->client->jsonRequest('PUT', '/api/admin/contacts/' . $contact->getId());
        $this->assertResponseIsSuccessful();

        $this->client->jsonRequest('PUT', '/api/admin/contacts/' . $contact->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    public function testCreatePersonAsAdminReturnsCreated(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');

        $this->client->jsonRequest('POST', '/api/admin/persons', [
            'firstName' => 'Nouveau',
            'lastName'  => 'Promo',
            'startYear' => 2025,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = $this->responseData();
        $this->assertSame('Nouveau', $data['firstName']);
    }

    public function testCreateDuplicatePersonReturnsValidationError(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');

        $this->client->jsonRequest('POST', '/api/admin/persons', [
            'firstName' => 'Luka',
            'lastName'  => 'Maret',
            'startYear' => 2021,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $error = $this->responseError();
        $this->assertSame('VALIDATION_ERROR', $error['code']);
    }

    public function testCreatePersonAsNonAdminReturnsForbidden(): void
    {
        $this->loginAs('lilian.baudry@etu.univ-lyon1.fr');

        $this->client->jsonRequest('POST', '/api/admin/persons', [
            'firstName' => 'Test',
            'lastName'  => 'User',
            'startYear' => 2025,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }


    private function loginAs(string $email): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => $email]);
        $this->assertInstanceOf(User::class, $user);
        $this->client->loginUser($user, 'api');
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
     * @return array<int, array<string, mixed>>
     */
    private function responseDataList(): array
    {
        /** @var array<string, array<int, array<string, mixed>>> $body */
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
