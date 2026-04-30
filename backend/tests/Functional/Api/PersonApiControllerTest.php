<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Person\Person;
use App\Entity\Person\User;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PersonApiControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();
    }


    public function testListReturnsAllPersonsAsSummaryDtos(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/persons');

        $this->assertResponseIsSuccessful();
        $items = $this->responseDataList();
        $this->assertNotEmpty($items);

        $first = $items[0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('firstName', $first);
        $this->assertArrayHasKey('lastName', $first);
        $this->assertArrayHasKey('startYear', $first);
        $this->assertArrayNotHasKey('godFathers', $first);
    }

    public function testListWithValidOrderByReturnsSuccess(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/persons?orderBy=firstName');

        $this->assertResponseIsSuccessful();
    }

    public function testListWithInvalidOrderByFallsBackToId(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/persons?orderBy=invalid');

        $this->assertResponseIsSuccessful();
    }


    public function testShowReturnsFullProfileWithRelations(): void
    {
        $person = $this->getFirstPerson();

        $this->client->request(Request::METHOD_GET, '/api/persons/' . $person->getId());

        $this->assertResponseIsSuccessful();
        $data = $this->responseData();
        $this->assertSame($person->getId(), $data['id']);
        $this->assertArrayHasKey('godFathers', $data);
        $this->assertArrayHasKey('godChildren', $data);
        $this->assertArrayHasKey('characteristics', $data);
    }

    public function testShowReturnsNotFoundForUnknownId(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/persons/999999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $error = $this->responseError();
        $this->assertSame('PERSON_NOT_FOUND', $error['code']);
    }


    public function testUpdateOwnProfileReturnsUpdatedPerson(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');
        $person = $this->getPersonByEmail('luka.maret@etu.univ-lyon1.fr');

        $this->client->jsonRequest('PUT', '/api/persons/' . $person->getId(), [
            'firstName'   => 'Luka',
            'lastName'    => 'Maret',
            'startYear'   => 2021,
            'biography'   => 'Biographie modifiée',
            'description' => 'Description modifiée',
        ]);

        $this->assertResponseIsSuccessful();
        $data = $this->responseData();
        $this->assertSame('Biographie modifiée', $data['biography']);
    }

    public function testUpdateAnotherPersonProfileReturnsForbidden(): void
    {
        $this->loginAs('lilian.baudry@etu.univ-lyon1.fr');
        $person = $this->getPersonByEmail('luka.maret@etu.univ-lyon1.fr');

        $this->client->jsonRequest('PUT', '/api/persons/' . $person->getId(), [
            'firstName' => 'Hacked',
            'lastName'  => 'Person',
            'startYear' => 2021,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateRequiresAuthentication(): void
    {
        $person = $this->getFirstPerson();

        $this->client->jsonRequest('PUT', '/api/persons/' . $person->getId(), [
            'firstName' => 'Luka',
            'lastName'  => 'Maret',
            'startYear' => 2021,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }


    public function testDeleteAsAdminReturnsNoContent(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');

        /** @var PersonRepository $personRepo */
        $personRepo = self::getContainer()->get(PersonRepository::class);
        $temp       = new Person();
        $temp->setFirstName('Temp')->setLastName('Delete')->setStartYear(2024);
        $personRepo->update($temp);

        $this->client->request(Request::METHOD_DELETE, '/api/persons/' . $temp->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteAsNonAdminReturnsForbidden(): void
    {
        $this->loginAs('lilian.baudry@etu.univ-lyon1.fr');
        $person = $this->getPersonByEmail('luka.maret@etu.univ-lyon1.fr');

        $this->client->request(Request::METHOD_DELETE, '/api/persons/' . $person->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }


    public function testExportOwnDataReturnsPersonData(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');
        $person = $this->getPersonByEmail('luka.maret@etu.univ-lyon1.fr');

        $this->client->request(Request::METHOD_GET, '/api/persons/' . $person->getId() . '/export');

        $this->assertResponseIsSuccessful();
        $data = $this->responseData();
        $this->assertSame($person->getId(), $data['id']);
    }

    public function testExportAnotherPersonDataReturnsForbidden(): void
    {
        $this->loginAs('lilian.baudry@etu.univ-lyon1.fr');
        $person = $this->getPersonByEmail('luka.maret@etu.univ-lyon1.fr');

        $this->client->request(Request::METHOD_GET, '/api/persons/' . $person->getId() . '/export');

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

    private function getFirstPerson(): Person
    {
        /** @var PersonRepository $personRepository */
        $personRepository = self::getContainer()->get(PersonRepository::class);
        $persons = $personRepository->getAll();
        $this->assertNotEmpty($persons);
        return $persons[0];
    }

    private function getPersonByEmail(string $email): Person
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => $email]);
        $this->assertInstanceOf(User::class, $user);
        $person = $user->getPerson();
        $this->assertInstanceOf(Person::class, $person);
        return $person;
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
