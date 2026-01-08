<?php

declare(strict_types=1);

namespace App\Service\Contact\Resolver;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Type;
use App\Entity\Person\Person;
use App\Repository\PersonRepository;
use App\Service\Contact\ContactResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class RemovePersonResolver extends AbstractController implements ContactResolverInterface
{
    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {
    }

    public function supports(Contact $contact): bool
    {
        return $contact->getType() === Type::REMOVE_PERSON;
    }

    public function resolve(Contact $contact): ?Response
    {
        $person = $this->personRepository->getByIdentity(
            $contact->getRelatedPersonFirstName(),
            $contact->getRelatedPersonLastName()
        );

        if ($person instanceof Person) {
            $this->personRepository->delete($person);
        } else {
            $this->addFlash('error', 'Personne non trouvée');
        }

        return null;
    }
}
