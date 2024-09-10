<?php

namespace App\Controller;

use App\Application\person\PersonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PersonController extends AbstractController
{
    public function __construct(
        private readonly PersonService $personService,
    ) {
    }

    #[Route('/personne/{id}', name: 'person')]
    public function index( int $id): Response
    {
        $person = $this->personService->getPersonById($id);

        if ($person === null) {
            return $this->redirectToRoute('error');
        }

        return $this->render(
            'person.html.twig', [
                'person' => $person,
            ]
        );
    }
}
