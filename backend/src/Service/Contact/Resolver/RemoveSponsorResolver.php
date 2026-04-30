<?php

declare(strict_types=1);

namespace App\Service\Contact\Resolver;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Type;
use App\Entity\Person\Person;
use App\Entity\Sponsor\Sponsor;
use App\Repository\PersonRepository;
use App\Repository\SponsorRepository;
use App\Service\Contact\ContactResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class RemoveSponsorResolver extends AbstractController implements ContactResolverInterface
{
    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly SponsorRepository $sponsorRepository,
    ) {
    }

    public function supports(Contact $contact): bool
    {
        return $contact->getType() === Type::REMOVE_SPONSOR;
    }

    public function resolve(Contact $contact): ?Response
    {
        $person1 = $this->personRepository->getByIdentity(
            $contact->getRelatedPersonFirstName(),
            $contact->getRelatedPersonLastName()
        );
        $person2 = $this->personRepository->getByIdentity(
            $contact->getRelatedPerson2FirstName(),
            $contact->getRelatedPerson2LastName()
        );

        if (!$person1 instanceof Person || !$person2 instanceof Person) {
            $this->addFlash('error', 'Personnes non trouvées');
            return $this->redirectToRoute('admin_contact');
        }

        $sponsor = $this->sponsorRepository->getByPeopleIds($person1->getId(), $person2->getId());

        if (!$sponsor instanceof Sponsor) {
            $this->addFlash('error', 'Parrainage non trouvé');
            return $this->redirectToRoute('admin_contact');
        }

        $this->sponsorRepository->delete($sponsor);

        return null;
    }
}
