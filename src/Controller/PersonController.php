<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Characteristic\CharacteristicType;
use App\Entity\Person\Person;
use App\Entity\Person\Role;
use App\Form\PersonFormType;
use App\Repository\CharacteristicTypeRepository;
use App\Repository\PersonRepository;
use App\Security\Voter\PersonVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/personne')]
class PersonController extends AbstractController
{
    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly CharacteristicTypeRepository $characteristicTypeRepository,
    ) {
    }

    #[Route('/{id}', name: 'person', methods: [Request::METHOD_GET])]
    public function index(Person $person): Response
    {
        return $this->render('person.html.twig', ['person' => $person]);
    }

    #[Route('/{id}/edit', name: 'person_edit', methods: [Request::METHOD_GET])]
    #[IsGranted(PersonVoter::EDIT, subject: 'person')]
    public function edit(Person $person): Response
    {
        /** @var CharacteristicType[] $allTypes */
        $allTypes = $this->characteristicTypeRepository->findAll();
        $person->createMissingCharacteristics($allTypes);

        $form = $this->createForm(PersonFormType::class, $person);
        $form->get('characteristics')->setData($person->getCharacteristics());
        return $this->render('editPerson.html.twig', [
            'person'              => $person,
            'form'                => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'person_edit_post', methods: [Request::METHOD_POST])]
    #[IsGranted(PersonVoter::EDIT, subject: 'person')]
    public function editPost(Person $person, Request $request): Response
    {
        $form = $this->createForm(PersonFormType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->personRepository->update($person);
            $this->addFlash('success', 'Personne modifiée');
            return $this->redirectToRoute('person', ['id' => $person->getId()]);
        }

        return $this->render('editPerson.html.twig', [
            'form'   => $form,
            'person' => $person,
        ]);
    }

    #[Route('/{id}', name: 'person_delete', methods: [Request::METHOD_DELETE])]
    #[IsGranted(Role::ADMIN->value)]
    public function delete(Person $person): Response
    {
        $this->personRepository->delete($person);
        $this->addFlash('success', 'Personne supprimée');
        return $this->redirectToRoute('home');
    }
}
