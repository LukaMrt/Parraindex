<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\person\PersonService;
use App\Application\sponsor\SponsorService;
use App\Infrastructure\old\router\Router;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SponsorController extends AbstractController
{
    public function __construct(
        private readonly SponsorService $sponsorService
    ) {
    }

    #[Route('/sponsor/{id}', name: 'sponsor')]
    public function index(int $id): Response
    {
        $sponsor = $this->sponsorService->getSponsorById($id);

        if (!$sponsor instanceof \App\Entity\Sponsor\Sponsor) {
            return $this->redirectToRoute('error');
        }

        return $this->render('sponsor.html.twig', [
            'sponsor' => $sponsor,
        ]);
    }
}
