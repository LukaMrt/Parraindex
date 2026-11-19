<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Traits\ApiTestTrait;
use App\Dto\Sponsor\SponsorTypeDto;
use App\Entity\Person\Person;
use App\Entity\Person\User;
use App\Entity\Sponsor\Sponsor;
use App\Entity\Sponsor\Type;
use App\Repository\PersonRepository;
use App\Repository\SponsorRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SponsorApiControllerTest extends WebTestCase
{
    use ApiTestTrait;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testShowReturnsFullSponsorData(): void
    {
        $sponsor = $this->getFirstSponsor();

        $this->client->request(Request::METHOD_GET, '/api/sponsors/' . $sponsor->getId());

        $this->assertResponseIsSuccessful();
        $data = $this->responseData();
        $this->assertSame($sponsor->getId(), $data['id']);
        $this->assertArrayHasKey('godFatherId', $data);
        $this->assertArrayHasKey('godChildId', $data);
        $this->assertArrayHasKey('type', $data);
    }

    public function testShowReturnsNotFoundForUnknownId(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/sponsors/999999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $error = $this->responseError();
        $this->assertSame('SPONSOR_NOT_FOUND', $error['code']);
    }

    public function testCreateSponsorAsAuthenticatedUserReturnsCreated(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');

        $persons   = $this->getAllPersons();
        $godFather = $persons[count($persons) - 2];
        $godChild  = $persons[count($persons) - 1];

        $this->client->jsonRequest('POST', '/api/sponsors', [
            'godFatherId' => $godFather->getId(),
            'godChildId'  => $godChild->getId(),
            'type'        => SponsorTypeDto::CLASSIC->value,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = $this->responseData();
        $this->assertSame($godFather->getId(), $data['godFatherId']);
        $this->assertSame($godChild->getId(), $data['godChildId']);
    }

    public function testCreateSponsorRequiresAuthentication(): void
    {
        $this->client->jsonRequest('POST', '/api/sponsors', [
            'godFatherId' => 1,
            'godChildId'  => 2,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCreateSponsorWithUnknownPersonReturnsNotFound(): void
    {
        $this->loginAs('luka.maret@etu.univ-lyon1.fr');

        $this->client->jsonRequest('POST', '/api/sponsors', [
            'godFatherId' => 999999,
            'godChildId'  => 999999,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateSponsorAsParticipantReturnsUpdatedData(): void
    {
        $luka    = $this->loginAs('luka.maret@etu.univ-lyon1.fr');
        $sponsor = $this->findSponsor($luka, involved: true);

        $this->assertInstanceOf(Person::class, $sponsor->getGodFather());
        $this->assertInstanceOf(Person::class, $sponsor->getGodChild());
        $this->client->jsonRequest('PUT', '/api/sponsors/' . $sponsor->getId(), [
            'godFatherId' => $sponsor->getGodFather()->getId(),
            'godChildId'  => $sponsor->getGodChild()->getId(),
            'type'        => SponsorTypeDto::CLASSIC->value,
            'description' => 'Description modifiée',
        ]);

        $this->assertResponseIsSuccessful();
        $data = $this->responseData();
        $this->assertSame('Description modifiée', $data['description']);
    }

    public function testUpdateSponsorAsUnrelatedUserReturnsForbidden(): void
    {
        $lilian  = $this->loginAs('lilian.baudry@etu.univ-lyon1.fr');
        $sponsor = $this->findSponsor($lilian, involved: false);

        $this->assertInstanceOf(Person::class, $sponsor->getGodFather());
        $this->assertInstanceOf(Person::class, $sponsor->getGodChild());
        $this->client->jsonRequest('PUT', '/api/sponsors/' . $sponsor->getId(), [
            'godFatherId' => $sponsor->getGodFather()->getId(),
            'godChildId'  => $sponsor->getGodChild()->getId(),
            'type'        => SponsorTypeDto::CLASSIC->value,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteSponsorAsParticipantReturnsNoContent(): void
    {
        $lukaUser = $this->loginAs('luka.maret@etu.univ-lyon1.fr');
        $luka     = $lukaUser->getPerson();
        $this->assertInstanceOf(Person::class, $luka);

        $persons = $this->getAllPersons();
        $other   = array_find($persons, fn($p): bool => $p->getId() !== $luka->getId());
        $this->assertInstanceOf(Person::class, $other);

        $sponsor = new Sponsor();
        $sponsor->setGodFather($luka)->setGodChild($other)->setType(Type::UNKNOWN)->setCreatedAt(new \DateTime());
        /** @var SponsorRepository $sponsorRepo */
        $sponsorRepo = self::getContainer()->get(SponsorRepository::class);
        $sponsorRepo->update($sponsor);

        $this->client->request(Request::METHOD_DELETE, '/api/sponsors/' . $sponsor->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteSponsorAsUnrelatedUserReturnsForbidden(): void
    {
        $lilian  = $this->loginAs('lilian.baudry@etu.univ-lyon1.fr');
        $sponsor = $this->findSponsor($lilian, involved: false);

        $this->client->request(Request::METHOD_DELETE, '/api/sponsors/' . $sponsor->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }


    /**
     * Sponsor des fixtures dont l'utilisateur est (ou n'est pas) parrain/filleul.
     */
    private function findSponsor(User $user, bool $involved): Sponsor
    {
        /** @var SponsorRepository $sponsorRepository */
        $sponsorRepository = self::getContainer()->get(SponsorRepository::class);
        $person = $user->getPerson();

        $sponsor = array_find(
            $sponsorRepository->findAll(),
            static fn (Sponsor $s): bool => $involved === (
                $s->getGodFather()?->equals($person) === true || $s->getGodChild()?->equals($person) === true
            ),
        );
        $this->assertInstanceOf(Sponsor::class, $sponsor);

        return $sponsor;
    }

    private function getFirstSponsor(): Sponsor
    {
        /** @var SponsorRepository $sponsorRepository */
        $sponsorRepository = self::getContainer()->get(SponsorRepository::class);
        $sponsors = $sponsorRepository->findAll();
        $this->assertNotEmpty($sponsors);
        return $sponsors[0];
    }

    /**
     * @return Person[]
     */
    private function getAllPersons(): array
    {
        /** @var PersonRepository $personRepository */
        $personRepository = self::getContainer()->get(PersonRepository::class);
        return $personRepository->getAll();
    }
}
