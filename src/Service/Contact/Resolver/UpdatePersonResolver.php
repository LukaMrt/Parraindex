<?php

declare(strict_types=1);

namespace App\Service\Contact\Resolver;

use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\Contact\Contact;
use App\Entity\Contact\Type;
use App\Entity\Person\Person;
use App\Repository\PersonRepository;
use App\Service\Contact\ContactResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class UpdatePersonResolver extends AbstractController implements ContactResolverInterface
{
    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {
    }

    public function supports(Contact $contact): bool
    {
        if ($contact->getType() === Type::UPDATE_PERSON) {
            return true;
        }
        return $contact->getType() === Type::CHOCKING_CONTENT;
    }

    public function resolve(Contact $contact): RedirectResponse
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
