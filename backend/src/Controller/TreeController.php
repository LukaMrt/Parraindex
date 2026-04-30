<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PersonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TreeController extends AbstractController
{
    public function __construct(
        private readonly PersonService $personService,
    ) {
    }

    #[Route(path: '/tree/', name: 'tree')]
    public function index(): Response
    {
        return $this->render('tree.html.twig', [
            'people' => $this->personService->getAllShuffled(),
        ]);
    }
}
