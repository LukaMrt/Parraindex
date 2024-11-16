<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\person\PersonService;
use App\Entity\Person\Person;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/personne')]
class PersonController extends AbstractController
{
    public function __construct(
        private readonly PersonService $personService,
    ) {
    }

    #[Route('/{id}', name: 'person')]
    public function index(int $id): Response
    {
        $person = $this->personService->getPersonById($id);

        if (!$person instanceof Person) {
            return $this->redirectToRoute('error');
        }

        return $this->render(
            'person.html.twig',
            [
                'person' => $person,
            ]
        );
    }
}
