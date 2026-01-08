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

final class UpdatePersonResolver extends AbstractController implements ContactResolverInterface
{
    public function __construct(
        private PersonRepository $personRepository,
    ) {
    }

    public function supports(Contact $contact): bool
    {
        return $contact->getType() === Type::UPDATE_PERSON
            || $contact->getType() === Type::CHOCKING_CONTENT;
    }

    public function resolve(Contact $contact): Response
    {
        $person = $this->personRepository->getByIdentity(
            $contact->getRelatedPersonFirstName(),
            $contact->getRelatedPersonLastName()
        );

        if ($person instanceof Person) {
            return $this->redirectToRoute('person_edit', ['id' => $person->getId()]);
        }

        $this->addFlash('error', 'Personne non trouvée');
        return $this->redirectToRoute('admin_contact');
    }
}
