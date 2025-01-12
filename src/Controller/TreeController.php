<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/tree')]
class TreeController extends AbstractController
{
    public function __construct(
        private readonly PersonRepository $personRepository
    ) {
    }

    #[Route(path: '/', name: 'tree')]
    public function index(): Response
    {
        $people = $this->personRepository->findAll();
        shuffle($people);
        return $this->render('tree.html.twig', ['people' => $people]);
    }
}
