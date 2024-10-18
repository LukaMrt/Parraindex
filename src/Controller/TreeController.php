<?php

namespace App\Controller;

use App\Application\person\PersonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TreeController extends AbstractController
{
    public function __construct(
        private readonly PersonService $personService,
    ) {
    }

    #[Route(path: '/tree', name: 'tree')]
    public function index(): Response
    {
        $people = $this->personService->getAllPeople();
        shuffle($people);
        return $this->render('tree.html.twig', ['people' => $people]);
    }
}
