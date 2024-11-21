<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Characteristic\Characteristic;
use App\Entity\Characteristic\CharacteristicType;
use App\Entity\Person\Person;
use App\Entity\Person\User;
use App\Form\PersonFormType;
use App\Repository\CharacteristicTypeRepository;
use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/personne')]
class PersonController extends AbstractController
{

    public function __construct(
        private readonly Security $security,
        private readonly PersonRepository $personRepository,
        private readonly CharacteristicTypeRepository $characteristicTypeRepository,
    ) {
    }

    #[Route('/{id}', name: 'person')]
    public function index(?Person $person): Response
    {
        if (!$person instanceof Person) {
            throw $this->createNotFoundException('Personne non trouvée');
        }

        return $this->render('person.html.twig', ['person' => $person]);
    }

    #[Route('/{id}/edit', name: 'person_edit', methods: [Request::METHOD_GET])]
    public function edit(?Person $person): Response
    {
        $response = $this->checkAccess($person);
        if ($response instanceof RedirectResponse) {
            return $response;
        }
        assert($person instanceof Person);

        /** @var CharacteristicType[] $allTypes */
        $allTypes = $this->characteristicTypeRepository->findAll();
        foreach ($allTypes as $type) {
            $exists = $person->getCharacteristics()
                ->exists(static fn (int $key, Characteristic $characteristic)
                    => $characteristic->getType()?->equals($type) ?? false);
            if ($exists) {
                continue;
            }

            $person->addCharacteristic((new Characteristic())->setVisible(false)->setType($type));
        }

        $form = $this->createForm(PersonFormType::class, $person);
        return $this->render('editPerson.html.twig', [
            'person'              => $person,
            'form'                => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'person_edit_post', methods: [Request::METHOD_POST])]
    public function editPost(?Person $person, Request $request): Response
    {
        $response = $this->checkAccess($person);
        if ($response instanceof RedirectResponse) {
            return $response;
        }

        $form = $this->createForm(PersonFormType::class, $person);
        $form->handleRequest($request);
        dump($request->request->all());
        dump($form);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Person $data */
            $data = $form->getData();
            $data->setStartYear($person->getStartYear())
                ->setBirthdate($person->getBirthdate())
                ->setGodFathers($person->getGodFathers())
                ->setGodChildren($person->getGodChildren());
            $this->personRepository->update($data);
            $this->addFlash('success', 'Personne modifiée');
            return $this->redirectToRoute('person', ['id' => $person->getId()]);
        }

        return $this->render('editPerson.html.twig', [
            'form'   => $form,
            'person' => $person,
        ]);
    }

    private function checkAccess(?Person $person): ?RedirectResponse
    {
        if (!$this->security->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login');
        }

        if (!$person instanceof Person) {
            throw $this->createNotFoundException('Personne non trouvée');
        }

        /** @var User $user */
        $user = $this->security->getUser();
        if (!$person->equals($user->getPerson()) && !$user->isAdmin()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cette personne');
        }

        return null;
    }
}
