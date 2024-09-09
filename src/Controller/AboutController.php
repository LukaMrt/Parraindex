<?php

namespace App\Controller;

use App\Application\person\PersonService;
use App\Entity\old\person\Identity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class AboutController extends AbstractController
{
    public function __construct(
        private readonly PersonService $personService
    ) {
    }

    #[Route('/about', name: 'about')]
    public function index(): void
    {
        $authors = [
            $this->personService->getPersonByIdentity(new Identity("Lilian", "Baudry")),
            $this->personService->getPersonByIdentity(new Identity("Melvyn", "Delpree")),
            $this->personService->getPersonByIdentity(new Identity("Vincent", "Chavot--Dambrun")),
            $this->personService->getPersonByIdentity(new Identity("Luka", "Maret"))
        ];

        $this->render('about.html.twig', ['authors' => $authors]);
    }
}
