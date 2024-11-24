<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\person\PersonService;
use App\Entity\Person\Person;
use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/about')]
class AboutController extends AbstractController
{
    private const array AUTHORS = [
        [
            'firstName' => "Lilian",
            'lastName'  => "Baudry",
        ],
        [
            'firstName' => "Melvyn",
            'lastName'  => "Delpree",
        ],
        [
            'firstName' => "Vincent",
            'lastName'  => "Chavot--Dambrun",
        ],
        [
            'firstName' => "Luka",
            'lastName'  => "Maret",
        ],
    ];

    public function __construct(
        private readonly PersonRepository $personRepository
    ) {
    }

    #[Route('/', name: 'about')]
    public function index(): Response
    {
        $authors = array_map(
            fn($author): ?Person => $this->personRepository->getByIdentity(
                $author['firstName'],
                $author['lastName']
            ),
            self::AUTHORS
        );

        return $this->render('about.html.twig', ['authors' => $authors]);
    }
}
