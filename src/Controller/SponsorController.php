<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Person\Role;
use App\Entity\Sponsor\Sponsor;
use App\Repository\SponsorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/parrainage')]
class SponsorController extends AbstractController
{
    public function __construct(
        private readonly SponsorRepository $sponsorRepository
    ) {
    }

    #[Route('/{id}', name: 'sponsor', methods: [Request::METHOD_GET])]
    public function index(int $id): Response
    {
        $sponsor = $this->sponsorRepository->find($id);

        if (!$sponsor instanceof Sponsor) {
            throw $this->createNotFoundException('Parrainage non trouvé');
        }

        return $this->render('sponsor.html.twig', ['sponsor' => $sponsor]);
    }

    #[Route('/{id}', name: 'sponsor_delete', methods: [Request::METHOD_DELETE])]
    #[IsGranted(Role::ADMIN->value)]
    public function delete(Sponsor $sponsor): Response
    {
        $this->sponsorRepository->delete($sponsor);
        $this->addFlash('success', 'Parrainage supprimé');
        return $this->redirectToRoute('home');
    }
}
