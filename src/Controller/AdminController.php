<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Type;
use App\Entity\Person\Person;
use App\Entity\Person\Role;
use App\Entity\Person\User;
use App\Entity\Sponsor\Sponsor;
use App\Repository\ContactRepository;
use App\Repository\PersonRepository;
use App\Repository\SponsorRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted(Role::ADMIN->value)]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly ContactRepository $contactRepository,
        private readonly PersonRepository $personRepository,
        private readonly SponsorRepository $sponsorRepository,
        private readonly UserRepository $userRepository,
        private readonly EmailVerifier $emailVerifier,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    #[Route('/contact', name: 'admin_contact', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        $list  = $this->contactRepository->getAll();
        $types = Type::allTitles();

        return $this->render('contactAdmin.html.twig', [
            'contacts'    => $list,
            'typeContact' => $types
        ]);
    }

    #[Route('/contact/{id}/delete', name: 'admin_contact_delete', methods: [Request::METHOD_GET])]
    public function delete(Contact $contact): Response
    {
        $contact->setResolutionDate(new \DateTime());
        $this->contactRepository->update($contact);

        $this->addFlash('success', 'Contact cloturé');
        return $this->redirectToRoute('admin_contact');
    }

    /**
     * @throws \Exception
     */
    #[Route('/contact/{id}/resolve', name: 'admin_contact_resolve', methods: [Request::METHOD_GET])]
    public function resolve(Contact $contact): Response
    {
        switch ($contact->getType()) {
            case Type::ADD_PERSON:
                $person = new Person()
                    ->setFirstName($contact->getRelatedPersonFirstName())
                    ->setLastName($contact->getRelatedPersonLastName())
                    ->setStartYear($contact->getEntryYear());
                $this->personRepository->create($person);
                break;

            case Type::UPDATE_PERSON:
                $person = $this->personRepository->getByIdentity(
                    $contact->getRelatedPersonFirstName(),
                    $contact->getRelatedPersonLastName()
                );
                return $this->redirectToRoute('person_edit', ['id' => $person->getId()]);

            case Type::REMOVE_PERSON:
                $person = $this->personRepository->getByIdentity(
                    $contact->getRelatedPersonFirstName(),
                    $contact->getRelatedPersonLastName()
                );
                $this->personRepository->delete($person);
                break;

            case Type::ADD_SPONSOR:
                $person1 = $this->personRepository->getByIdentity(
                    $contact->getRelatedPersonFirstName(),
                    $contact->getRelatedPersonLastName()
                );
                $person2 = $this->personRepository->getByIdentity(
                    $contact->getRelatedPerson2FirstName(),
                    $contact->getRelatedPerson2LastName()
                );
                $sponsor = new Sponsor()
                    ->setGodFather($person1)
                    ->setGodChild($person2)
                    ->setType($contact->getSponsorType())
                    ->setDate($contact->getSponsorDate())
                    ->setDescription($contact->getDescription());
                $this->sponsorRepository->create($sponsor);
                break;

            case Type::UPDATE_SPONSOR:
                $person1 = $this->personRepository->getByIdentity(
                    $contact->getRelatedPersonFirstName(),
                    $contact->getRelatedPersonLastName()
                );
                $person2 = $this->personRepository->getByIdentity(
                    $contact->getRelatedPerson2FirstName(),
                    $contact->getRelatedPerson2LastName()
                );
                if ($person1 === null || $person2 === null) {
                    throw $this->createNotFoundException('Personnes non trouvées');
                }
                $sponsor = $this->sponsorRepository->getByPeopleIds($person1->getId(), $person2->getId());
                return $this->redirectToRoute('sponsor_edit', ['id' => $sponsor->getId()]);

            case Type::REMOVE_SPONSOR:
                $person1 = $this->personRepository->getByIdentity(
                    $contact->getRelatedPersonFirstName(),
                    $contact->getRelatedPersonLastName()
                );
                $person2 = $this->personRepository->getByIdentity(
                    $contact->getRelatedPerson2FirstName(),
                    $contact->getRelatedPerson2LastName()
                );
                $sponsor = $this->sponsorRepository->getByPeopleIds($person1->getId(), $person2->getId());
                $this->sponsorRepository->delete($sponsor);
                break;

            case Type::CHOCKING_CONTENT:
                $person = $this->personRepository->getByIdentity(
                    $contact->getRelatedPersonFirstName(),
                    $contact->getRelatedPersonLastName()
                );
                return $this->redirectToRoute('person_edit', ['id' => $person->getId()]);

            case Type::PASSWORD:
                $person = $this->personRepository->getByIdentity(
                    $contact->getContacterFirstName(),
                    $contact->getContacterLastName()
                );
                if ($person === null) {
                    throw $this->createNotFoundException('Personne non trouvée');
                }
                $user = new User()->setPerson($person)->setEmail($contact->getContacterEmail());
                $user->setPassword($this->userPasswordHasher->hashPassword($user, $contact->getPassword()));

                $this->userRepository->update($user);
                $this->emailVerifier->sendEmailConfirmation(
                    'register_verify',
                    $user,
                    new TemplatedEmail()
                        ->to((string)$user->getEmail())
                        ->subject('Confirmez votre email')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );
                break;

            case Type::BUG:
            case Type::OTHER:
                break;
            default:
                throw new \Exception('Type de contact inconnu');
        }

//        $contact->setResolutionDate(new \DateTime());
//        $this->contactRepository->update($contact);
        $this->addFlash('success', 'Contact résolu');
        return $this->redirectToRoute('admin_contact');
    }
}
