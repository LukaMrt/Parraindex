<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\File\UploadedFile;
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


    public function testDownloadImportTemplateAsAdminReturnsCsv(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');

        $this->client->request(Request::METHOD_GET, '/api/admin/persons/import/template');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'text/csv; charset=utf-8');

        $content = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('firstName', $content);
        $this->assertStringContainsString('lastName', $content);
        $this->assertStringContainsString('startYear', $content);
        $this->assertStringContainsString('godFatherFirstName', $content);
    }

    public function testDownloadImportTemplateRequiresAuthentication(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/admin/persons/import/template');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testImportCsvWithValidDataAsAdminReturnsSuccess(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');

        $csvContent = "firstName,lastName,startYear,biography,description,color,godFatherFirstName,godFatherLastName,sponsorType,sponsorDate,sponsorDescription\n"
            . "Import,Testperson,2025,,,,,,,\n";

        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        $this->assertNotFalse($tmpFile);
        file_put_contents($tmpFile, $csvContent);

        $this->client->request(
            Request::METHOD_POST,
            '/api/admin/persons/import/csv',
            [],
            ['file' => new UploadedFile($tmpFile, 'import.csv', 'text/csv', null, true)],
        );

        unlink($tmpFile);

        $this->assertResponseIsSuccessful();
        $data = $this->responseData();
        $this->assertArrayHasKey('personsCreated', $data);
        $this->assertArrayHasKey('personsSkipped', $data);
        $this->assertArrayHasKey('sponsorsCreated', $data);
        $this->assertArrayHasKey('errors', $data);
        $this->assertSame(1, $data['personsCreated']);
        $this->assertSame(0, $data['personsSkipped']);
    }

    public function testImportCsvWithSponsorLinkCreatesLink(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');

        $csvContent = "firstName,lastName,startYear,biography,description,color,godFatherFirstName,godFatherLastName,sponsorType,sponsorDate,sponsorDescription\n"
            . "CsvParrain,Test,2025,,,,,,,,\n"
            . "CsvFilleul,Test,2025,,,,CsvParrain,Test,CLASSIC,2025-01-01,Test parrainage\n";

        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_sponsor_test_');
        $this->assertNotFalse($tmpFile);
        file_put_contents($tmpFile, $csvContent);

        $this->client->request(
            Request::METHOD_POST,
            '/api/admin/persons/import/csv',
            [],
            ['file' => new UploadedFile($tmpFile, 'import.csv', 'text/csv', null, true)],
        );

        unlink($tmpFile);

        $this->assertResponseIsSuccessful();
        $data = $this->responseData();
        $this->assertSame(2, $data['personsCreated']);
        $this->assertSame(1, $data['sponsorsCreated']);
    }

    public function testImportCsvWithoutFileReturnsError(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');

        $this->client->request(Request::METHOD_POST, '/api/admin/persons/import/csv');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testImportCsvWithMissingRequiredHeadersReturnsError(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');

        $csvContent = "nom,prenom\nDupont,Jean\n";

        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_bad_test_');
        $this->assertNotFalse($tmpFile);
        file_put_contents($tmpFile, $csvContent);

        $this->client->request(
            Request::METHOD_POST,
            '/api/admin/persons/import/csv',
            [],
            ['file' => new UploadedFile($tmpFile, 'import.csv', 'text/csv', null, true)],
        );

        unlink($tmpFile);

        $this->assertResponseIsSuccessful();
        $data = $this->responseData();
        $this->assertNotEmpty($data['errors']);
    }

    public function testImportCsvAsNonAdminReturnsForbidden(): void
    {
        $this->loginAs('lilian.baudry@etu.univ-lyon1.fr');

        $this->client->request(Request::METHOD_POST, '/api/admin/persons/import/csv');

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
