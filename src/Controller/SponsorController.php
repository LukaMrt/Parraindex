<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\sponsor\SponsorService;
use App\Entity\Sponsor\Sponsor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sponsor')]
class SponsorController extends AbstractController
{
    public function __construct(
        private readonly SponsorService $sponsorService
    ) {
    }

    #[Route('/{id}', name: 'sponsor')]
    public function index(int $id): Response
    {
        $sponsor = $this->sponsorService->getSponsorById($id);

        if (!$sponsor instanceof Sponsor) {
            return $this->redirectToRoute('error');
        }

        return $this->render('sponsor.html.twig', [
            'sponsor' => $sponsor,
        ]);
    }
}
