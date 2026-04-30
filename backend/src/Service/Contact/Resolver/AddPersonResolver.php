<?php

declare(strict_types=1);

namespace App\Service\Contact\Resolver;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Type;
use App\Entity\Person\Person;
use App\Repository\PersonRepository;
use App\Service\Contact\ContactResolverInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class AddPersonResolver implements ContactResolverInterface
{
    public function __construct(
        private PersonRepository $personRepository,
    ) {
    }

    public function supports(Contact $contact): bool
    {
        return $contact->getType() === Type::ADD_PERSON;
    }

    public function resolve(Contact $contact): ?Response
    {
        $person = new Person()
            ->setFirstName($contact->getRelatedPersonFirstName())
            ->setLastName($contact->getRelatedPersonLastName())
            ->setStartYear($contact->getEntryYear());

        $this->personRepository->create($person);

        return null;
    }
}
