<?php

namespace App\Controller;

use App\Application\person\PersonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AboutController extends AbstractController
{
    public function __construct(
        private readonly PersonService $personService
    ) {
    }

    #[Route('/about', name: 'about')]
    public function index(): Response
    {
        $authors = [
            $this->personService->getPersonByIdentity("Lilian", "Baudry"),
            $this->personService->getPersonByIdentity("Melvyn", "Delpree"),
            $this->personService->getPersonByIdentity("Vincent", "Chavot--Dambrun"),
            $this->personService->getPersonByIdentity("Luka", "Maret"),
        ];

        return $this->render('about.html.twig', ['authors' => $authors]);
    }
}
