<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Person\Person;
use App\Entity\Person\Role;
use App\Form\PersonFormType;
use App\Security\Voter\PersonVoter;
use App\Service\PersonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PersonController extends AbstractController
{
    public function __construct(
        private readonly PersonService $personService,
    ) {
    }

    #[Route('/personne/{id}', name: 'person', methods: [Request::METHOD_GET])]
    public function index(int $id): Response
    {
        $person = $this->personService->getWithRelations($id);

        if (!$person instanceof Person) {
            throw $this->createNotFoundException('Person not found');
        }

        return $this->render('person.html.twig', ['person' => $person]);
    }

    #[Route('/personne/{id}/edit', name: 'person_edit', methods: [Request::METHOD_GET])]
    #[IsGranted(PersonVoter::EDIT, subject: 'person')]
    public function edit(Person $person): Response
    {
        $this->personService->prepareMissingCharacteristics($person);

        $form = $this->createForm(PersonFormType::class, $person);
        $form->get('characteristics')->setData($person->getCharacteristics());

        return $this->render('editPerson.html.twig', [
            'person' => $person,
            'form'   => $form,
        ]);
    }

    #[Route('/personne/{id}/edit', name: 'person_edit_post', methods: [Request::METHOD_POST])]
    #[IsGranted(PersonVoter::EDIT, subject: 'person')]
    public function editPost(Person $person, Request $request): Response
    {
        $form = $this->createForm(PersonFormType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->personService->update($person);
            $this->addFlash('success', 'Personne modifiée');

            return $this->redirectToRoute('person', ['id' => $person->getId()]);
        }

        return $this->render('editPerson.html.twig', [
            'form'   => $form,
            'person' => $person,
        ]);
    }

    #[Route('/personne/{id}', name: 'person_delete', methods: [Request::METHOD_DELETE])]
    #[IsGranted(Role::ADMIN->value)]
    public function delete(Person $person): Response
    {
        $this->personService->delete($person);
        $this->addFlash('success', 'Personne supprimée');

        return $this->redirectToRoute('home');
    }
}
