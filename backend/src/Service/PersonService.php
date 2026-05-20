<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Person\CharacteristicDto;
use App\Dto\Person\FiliereRequestDto;
use App\Dto\Person\FiliereResponseDto;
use App\Dto\Person\PersonResponseDto;
use App\Dto\Sponsor\SponsorResponseDto;
use App\Entity\Characteristic\Characteristic;
use App\Entity\Characteristic\CharacteristicType;
use App\Entity\Person\Person;
use App\Entity\Sponsor\Sponsor;
use App\Entity\Person\Filiere;
use App\Entity\Person\PersonFiliere;
use App\Repository\CharacteristicTypeRepository;
use App\Repository\Person\FiliereRepository;
use App\Repository\PersonRepository;

final readonly class PersonService
{
    public function __construct(
        private PersonRepository $personRepository,
        private CharacteristicTypeRepository $characteristicTypeRepository,
        private FiliereRepository $filiereRepository,
    ) {
    }

    public function getById(int $id): ?Person
    {
        return $this->personRepository->getById($id);
    }

    public function getWithRelations(int $id): ?Person
    {
        return $this->personRepository->findWithRelations($id);
    }

    /**
     * @param 'id'|'firstName'|'lastName'|'startYear'|'createdAt' $orderBy
     * @return Person[]
     */
    public function getAll(string $orderBy = 'id'): array
    {
        return $this->personRepository->getAll($orderBy);
    }

    public function findByIdentity(string $firstName, string $lastName): ?Person
    {
        return $this->personRepository->getByIdentity($firstName, $lastName);
    }

    public function prepareMissingCharacteristics(Person $person): void
    {
        /** @var CharacteristicType[] $allTypes */
        $allTypes = $this->characteristicTypeRepository->findAll();
        $person->createMissingCharacteristics($allTypes);
    }

    /**
     * @return Person[]
     */
    public function getAllWithSponsors(): array
    {
        return $this->personRepository->findAllWithSponsors();
    }

    /**
     * @return Person[]
     */
    public function getPaginated(int $offset, int $limit): array
    {
        return $this->personRepository->findPaginated($offset, $limit);
    }

    public function countAll(): int
    {
        return $this->personRepository->countAll();
    }

    public function update(Person $person): void
    {
        $this->personRepository->update($person);
    }

    public function delete(Person $person): void
    {
        $this->personRepository->delete($person);
    }

    /**
     * @return Person[]
     */
    public function getAllShuffled(): array
    {
        $people = $this->personRepository->findAll();
        shuffle($people);

        return $people;
    }

    /**
     * @param int[] $ids
     * @return Person[]
     */
    public function getByIds(array $ids): array
    {
        return $this->personRepository->findAllWithRelationsByIds($ids);
    }

    public function mapToResponseDto(Person $person): PersonResponseDto
    {
        $filename = $person->getPicture();
        $picture  = $filename !== null && str_ends_with($filename, '.gif')
            ? '/uploads/avatars/' . $filename
            : $filename;

        return new PersonResponseDto(
            id: $person->getId(),
            firstName: $person->getFirstName(),
            lastName: $person->getLastName(),
            fullName: $person->getFullName(),
            picture: $picture,
            startYear: $person->getStartYear() ?? 0,
            birthdate: $person->getBirthdate()?->format('Y-m-d'),
            biography: $person->getBiography(),
            description: $person->getDescription(),
            godFathers: array_map($this->mapSponsorToResponseDto(...), $person->getGodFathers()->toArray()),
            godChildren: array_map($this->mapSponsorToResponseDto(...), $person->getGodChildren()->toArray()),
            characteristics: array_filter(
                array_map($this->mapCharacteristicToDto(...), $person->getCharacteristics()->toArray()),
                static fn(?CharacteristicDto $c): bool => $c instanceof CharacteristicDto,
            ),
            filieres: array_map(
                static fn(PersonFiliere $personFiliere): FiliereResponseDto => new FiliereResponseDto(
                    name: $personFiliere->getFiliere()?->getName() ?? throw new \LogicException('PersonFiliere has no Filiere.'),
                    startYear: $personFiliere->getStartYear(),
                    endYear: $personFiliere->getEndYear(),
                ),
                $person->getFilieres()->toArray()
            )
        );
    }

    private function mapSponsorToResponseDto(Sponsor $sponsor): SponsorResponseDto
    {
        $godFather = $sponsor->getGodFather() ?? throw new \LogicException('Sponsor has no godFather.');
        $godChild  = $sponsor->getGodChild() ?? throw new \LogicException('Sponsor has no godChild.');

        return new SponsorResponseDto(
            id: (int) $sponsor->getId(),
            godFatherId: $godFather->getId(),
            godFatherName: $godFather->getFullName(),
            godChildId: $godChild->getId(),
            godChildName: $godChild->getFullName(),
            type: $sponsor->getType()->name ?? '',
            date: $sponsor->getDate()?->format('Y-m-d'),
            description: $sponsor->getDescription(),
        );
    }

    private function mapCharacteristicToDto(Characteristic $characteristic): ?CharacteristicDto
    {
        $type = $characteristic->getType();

        if (!$type instanceof CharacteristicType) {
            return null;
        }

        return new CharacteristicDto(
            id: (int) $characteristic->getId(),
            value: $characteristic->getValue(),
            visible: (bool) $characteristic->isVisible(),
            typeTitle: $type->getTitle() ?? '',
            typeUrl: $type->getUrl(),
            typeImage: $type->getImage(),
        );
    }

    /**
     * @param FiliereRequestDto[] $filiereDtos
     */
    public function syncFilieres(Person $person, array $filiereDtos): void
    {
        $person->replaceFilieres(array_map($this->buildPersonFiliere(...), $filiereDtos));
    }

    private function buildPersonFiliere(FiliereRequestDto $dto): PersonFiliere
    {
        $canonical = Filiere::normalize($dto->name);

        $filiere = $this->filiereRepository->findByName($canonical)
            ?? new Filiere()->setName($canonical);

        $personFiliere = new PersonFiliere();
        $personFiliere->setFiliere($filiere);
        $personFiliere->setStartYear($dto->startYear ?? throw new \InvalidArgumentException('startYear is required.'));
        $personFiliere->setEndYear($dto->endYear);

        return $personFiliere;
    }
}
