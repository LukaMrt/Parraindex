<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Characteristic\Characteristic;
use App\Entity\Characteristic\CharacteristicType;
use App\Entity\Person\Person;
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

    #[Route('/{id}', name: 'person')]
    public function index(Person $person): Response
    {
        return $this->render('person.html.twig', ['person' => $person]);
    }

    #[Route('/{id}/edit', name: 'person_edit', methods: [Request::METHOD_GET])]
    #[IsGranted(PersonVoter::PERSON_EDIT, subject: 'person')]
    public function edit(Person $person): Response
    {
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
        dump($form);
        return $this->render('editPerson.html.twig', [
            'person'              => $person,
            'form'                => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'person_edit_post', methods: [Request::METHOD_POST])]
    #[IsGranted(PersonVoter::PERSON_EDIT, subject: 'person')]
    public function editPost(Person $person, Request $request): Response
    {
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
}
