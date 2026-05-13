<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Person\Person;
use App\Entity\Sponsor\Sponsor;
use App\Entity\Sponsor\Type as SponsorType;
use App\Repository\PersonRepository;
use App\Repository\SponsorRepository;

final readonly class CsvImportService
{
    private const array REQUIRED_HEADERS = [
        'firstName',
        'lastName',
        'startYear',
    ];

    private const array OPTIONAL_PERSON_HEADERS = [
        'biography',
        'description',
    ];

    private const array SPONSOR_HEADERS = [
        'godFatherFirstName',
        'godFatherLastName',
        'sponsorType',
        'sponsorDate',
        'sponsorDescription',
    ];

    public function __construct(
        private PersonRepository $personRepository,
        private SponsorRepository $sponsorRepository,
    ) {
    }

    /**
     * @return array{
     *   personsCreated: int,
     *   personsSkipped: int,
     *   sponsorsCreated: int,
     *   sponsorsSkipped: int,
     *   errors: string[]
     * }
     */
    public function import(string $csvContent): array
    {
        $result = [
            'personsCreated' => 0,
            'personsSkipped' => 0,
            'sponsorsCreated' => 0,
            'sponsorsSkipped' => 0,
            'errors' => [],
        ];

        $rows = $this->parseCsv($csvContent);

        if ($rows === []) {
            $result['errors'][] = 'Le fichier CSV est vide ou ne contient que l\'en-tête.';
            return $result;
        }

        $headers = array_shift($rows);

        if (!$this->validateHeaders($headers)) {
            $result['errors'][] = sprintf(
                'En-têtes manquants. Requis: %s',
                implode(', ', self::REQUIRED_HEADERS),
            );
            return $result;
        }

        /** @var Person[] $importedPersons */
        $importedPersons = [];

        foreach ($rows as $rowIndex => $row) {
            $lineNumber = $rowIndex + 2;

            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }

            /** @var array<string, string> $data */
            $data = array_combine($headers, $row);

            $personResult = $this->importPerson($data, $lineNumber, $importedPersons);

            if ($personResult === null) {
                ++$result['personsSkipped'];
            } elseif (is_string($personResult)) {
                $result['errors'][] = $personResult;
            } else {
                ++$result['personsCreated'];
                $importedPersons[] = $personResult;
            }

            $sponsorResult = $this->importSponsor($data, $lineNumber, $importedPersons);

            if ($sponsorResult === true) {
                ++$result['sponsorsCreated'];
            } elseif ($sponsorResult === false) {
                ++$result['sponsorsSkipped'];
            } elseif (is_string($sponsorResult)) {
                $result['errors'][] = $sponsorResult;
            }
        }

        return $result;
    }

    public function generateTemplate(): string
    {
        $headers = array_merge(
            self::REQUIRED_HEADERS,
            self::OPTIONAL_PERSON_HEADERS,
            self::SPONSOR_HEADERS,
        );

        $example = [
            'Jean',
            'Dupont',
            '2020',
            'Biographie optionnelle',
            'Description optionnelle',
            '',
            '',
            '',
            '',
            '',
        ];

        $exampleWithGodFather = [
            'Marie',
            'Martin',
            '2021',
            '',
            '',
            'Jean',
            'Dupont',
            'CLASSIC',
            '2021-09-01',
            'Parrainage de promotion',
        ];

        return implode("\n", [
            implode(',', $headers),
            implode(',', $example),
            implode(',', $exampleWithGodFather),
        ]) . "\n";
    }

    /**
     * @param array<string, string> $data
     * @param Person[]              $importedPersons
     * @return Person|string|null Person if created, string error if failed, null if skipped (already exists)
     */
    private function importPerson(array $data, int $lineNumber, array &$importedPersons): Person|string|null
    {
        $firstName = trim($data['firstName'] ?? '');
        $lastName  = trim($data['lastName'] ?? '');
        $startYear = trim($data['startYear'] ?? '');

        if ($firstName === '' || $lastName === '') {
            return sprintf('Ligne %d: prénom et nom sont obligatoires.', $lineNumber);
        }

        if (!is_numeric($startYear) || (int) $startYear < 1900 || (int) $startYear > 2100) {
            return sprintf('Ligne %d: année d\'entrée invalide "%s" (doit être entre 1900 et 2100).', $lineNumber, $startYear);
        }

        $normalizedFirst = ucfirst(strtolower($firstName));
        $normalizedLast  = ucfirst(strtolower($lastName));

        $existing = $this->personRepository->getByIdentity($normalizedFirst, $normalizedLast);

        if ($existing instanceof Person) {
            // Add to importedPersons so they can be referenced as godFathers
            if (!in_array($existing, $importedPersons, true)) {
                $importedPersons[] = $existing;
            }

            return null;
        }

        $person = new Person();
        $person->setFirstName($firstName)
            ->setLastName($lastName)
            ->setStartYear((int) $startYear);

        $biography = trim($data['biography'] ?? '');
        if ($biography !== '') {
            $person->setBiography($biography);
        }

        $description = trim($data['description'] ?? '');
        if ($description !== '') {
            $person->setDescription($description);
        }

        $this->personRepository->update($person);

        return $person;
    }

    /**
     * @param array<string, string> $data
     * @param Person[]              $importedPersons
     * @return true|false|string|null true if created, false if skipped, string if error, null if no sponsor data
     */
    private function importSponsor(array $data, int $lineNumber, array $importedPersons): bool|string|null
    {
        $godFatherFirstName = trim($data['godFatherFirstName'] ?? '');
        $godFatherLastName  = trim($data['godFatherLastName'] ?? '');

        if ($godFatherFirstName === '' || $godFatherLastName === '') {
            return null;
        }

        $childFirstName = trim($data['firstName'] ?? '');
        $childLastName  = trim($data['lastName'] ?? '');

        $godChild = $this->findPerson($childFirstName, $childLastName, $importedPersons);
        if (!$godChild instanceof Person) {
            return sprintf('Ligne %d: le filleul "%s %s" est introuvable.', $lineNumber, $childFirstName, $childLastName);
        }

        $godFather = $this->findPerson($godFatherFirstName, $godFatherLastName, $importedPersons);
        if (!$godFather instanceof Person) {
            return sprintf('Ligne %d: le parrain "%s %s" est introuvable.', $lineNumber, $godFatherFirstName, $godFatherLastName);
        }

        $existing = $this->sponsorRepository->getByPeopleIds($godFather->getId(), $godChild->getId());
        if ($existing instanceof Sponsor) {
            return false;
        }

        $sponsor = new Sponsor();
        $sponsor->setGodFather($godFather)
            ->setGodChild($godChild)
            ->setType($this->parseSponsorType($data['sponsorType'] ?? ''));

        $sponsorDate = trim($data['sponsorDate'] ?? '');
        if ($sponsorDate !== '') {
            $date = \DateTime::createFromFormat('Y-m-d', $sponsorDate);
            if ($date instanceof \DateTime) {
                $sponsor->setDate($date);
            }
        }

        $sponsorDescription = trim($data['sponsorDescription'] ?? '');
        if ($sponsorDescription !== '') {
            $sponsor->setDescription($sponsorDescription);
        }

        $this->sponsorRepository->update($sponsor);

        return true;
    }

    /**
     * @param Person[] $importedPersons
     */
    private function findPerson(string $firstName, string $lastName, array $importedPersons): ?Person
    {
        $normalizedFirst = ucfirst(strtolower($firstName));
        $normalizedLast  = ucfirst(strtolower($lastName));

        foreach ($importedPersons as $person) {
            if ($person->getFirstName() === $normalizedFirst && $person->getLastName() === $normalizedLast) {
                return $person;
            }
        }

        return $this->personRepository->getByIdentity($normalizedFirst, $normalizedLast);
    }

    private function parseSponsorType(string $type): SponsorType
    {
        return match (strtoupper(trim($type))) {
            'HEART'   => SponsorType::HEART,
            'CLASSIC' => SponsorType::CLASSIC,
            default   => SponsorType::UNKNOWN,
        };
    }

    /**
     * @param string[] $headers
     */
    private function validateHeaders(array $headers): bool
    {
        return array_all(self::REQUIRED_HEADERS, fn($required): bool => in_array($required, $headers, true));
    }

    /**
     * @return string[][]
     */
    private function parseCsv(string $content): array
    {
        $content = str_replace("\r\n", "\n", $content);
        $content = str_replace("\r", "\n", $content);

        $lines = array_filter(
            explode("\n", $content),
            static fn(string $line): bool => trim($line) !== '',
        );

        return array_map(
            static function (string $line): array {
                /** @var string[] $parsed */
                $parsed = str_getcsv($line, escape: '\\');
                return $parsed;
            },
            array_values($lines),
        );
    }
}
