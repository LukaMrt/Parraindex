<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\person\PersonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AboutController extends AbstractController
{
    private const array AUTHORS = [
        ['firstName' => "Lilian", 'lastName' => "Baudry"],
        ['firstName' => "Melvyn", 'lastName' => "Delpree"],
        ['firstName' => "Vincent", 'lastName' => "Chavot--Dambrun"],
        ['firstName' => "Luka", 'lastName' => "Maret"],
    ];

    public function __construct(
        private readonly PersonService $personService
    ) {
    }

    #[Route('/about', name: 'about')]
    public function index(): Response
    {
        $authors = array_map(
            static fn($author) => $this->personService->getPersonByIdentity($author['firstName'], $author['lastName']),
            self::AUTHORS
        );

        return $this->render('about.html.twig', ['authors' => $authors]);
    }
}
