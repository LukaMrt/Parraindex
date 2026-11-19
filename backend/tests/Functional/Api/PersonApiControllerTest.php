<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Traits\ApiTestTrait;
use App\Entity\Person\Person;
use App\Entity\Person\User;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PersonApiControllerTest extends WebTestCase
{
    use ApiTestTrait;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testListWithoutQueryReturnsEmptyList(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/persons');

        self::assertResponseIsSuccessful();
        self::assertSame([], $this->responseDataList());
    }

    public function testSearchReturnsMatchingPersons(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/persons?q=Maret');

        self::assertResponseIsSuccessful();
        $items = $this->responseDataList();
        self::assertNotEmpty($items);

        $first = $items[0];
        self::assertArrayHasKey('id', $first);
        self::assertArrayHasKey('firstName', $first);
        self::assertArrayHasKey('lastName', $first);
        self::assertArrayHasKey('startYear', $first);
        self::assertSame('Maret', $first['lastName']);
    }

    public function testListWithValidOrderByReturnsSuccess(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/persons?q=a&orderBy=firstName');

        self::assertResponseIsSuccessful();
    }

    public function testListWithInvalidOrderByFallsBackToId(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/persons?q=a&orderBy=invalid');

        self::assertResponseIsSuccessful();
    }

    public function testShowReturnsFullProfileWithRelations(): void
    {
        $person = $this->getFirstPerson();

        $this->client->request(Request::METHOD_GET, '/api/persons/' . $person->getId());

        self::assertResponseIsSuccessful();
        $data = $this->responseData();
        self::assertSame($person->getId(), $data['id']);
        self::assertArrayHasKey('godFathers', $data);
        self::assertArrayHasKey('godChildren', $data);
        self::assertArrayHasKey('characteristics', $data);
    }

    public function testShowReturnsNotFoundForUnknownId(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/persons/999999');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $error = $this->responseError();
        self::assertSame('PERSON_NOT_FOUND', $error['code']);
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

        self::assertResponseIsSuccessful();
        $data = $this->responseData();
        self::assertSame('Biographie modifiée', $data['biography']);
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

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateRequiresAuthentication(): void
    {
        $person = $this->getFirstPerson();

        $this->client->jsonRequest('PUT', '/api/persons/' . $person->getId(), [
            'firstName' => 'Luka',
            'lastName'  => 'Maret',
            'startYear' => 2021,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
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

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteAsNonAdminReturnsForbidden(): void
    {
        $this->loginAs('lilian.baudry@etu.univ-lyon1.fr');
        $person = $this->getPersonByEmail('luka.maret@etu.univ-lyon1.fr');

        $this->client->request(Request::METHOD_DELETE, '/api/persons/' . $person->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    private function getFirstPerson(): Person
    {
        /** @var PersonRepository $personRepository */
        $personRepository = self::getContainer()->get(PersonRepository::class);
        $persons = $personRepository->getAll();
        self::assertNotEmpty($persons);
        return $persons[0];
    }

    private function getPersonByEmail(string $email): Person
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => $email]);
        self::assertInstanceOf(User::class, $user);
        $person = $user->getPerson();
        self::assertInstanceOf(Person::class, $person);
        return $person;
    }
}
