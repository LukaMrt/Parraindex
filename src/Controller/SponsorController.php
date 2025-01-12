<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Sponsor\Sponsor;
use App\Entity\Sponsor\Type;
use App\Form\SponsorType;
use App\Repository\SponsorRepository;
use App\Security\Voter\SponsorVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/parrainage')]
class SponsorController extends AbstractController
{
    public function __construct(
        private readonly SponsorRepository $sponsorRepository,
    ) {
    }

    #[Route('/{id}', name: 'sponsor', methods: [Request::METHOD_GET])]
    public function index(Sponsor $sponsor): Response
    {
        return $this->render('sponsor.html.twig', ['sponsor' => $sponsor]);
    }

    #[Route('/{id}/edit', name: 'sponsor_edit', methods: [Request::METHOD_GET])]
    #[IsGranted(SponsorVoter::EDIT, subject: 'sponsor')]
    public function edit(Sponsor $sponsor): Response
    {
        $form = $this->createForm(SponsorType::class, $sponsor);
        return $this->render(
            'editSponsor.html.twig',
            [
                'sponsor'  => $sponsor,
                'allTypes' => Type::allTitles(),
                'form'     => $form,
            ],
        );
    }

    #[Route('/{id}/edit', name: 'sponsor_edit_post', methods: [Request::METHOD_POST])]
    #[IsGranted(SponsorVoter::EDIT, subject: 'sponsor')]
    public function editPost(Sponsor $sponsor, Request $request): Response
    {
        $form = $this->createForm(SponsorType::class, $sponsor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->sponsorRepository->update($sponsor);
            $this->addFlash('success', 'Lien modifié');
            return $this->redirectToRoute('sponsor', ['id' => $sponsor->getId()]);
        }

        return $this->redirectToRoute('sponsor_edit', ['id' => $sponsor->getId()]);
    }


    #[Route('/{id}', name: 'sponsor_delete', methods: [Request::METHOD_DELETE])]
    #[IsGranted(SponsorVoter::EDIT, subject: 'sponsor')]
    public function delete(Sponsor $sponsor): Response
    {
        $this->sponsorRepository->delete($sponsor);
        $this->addFlash('success', 'Parrainage supprimé');
        return $this->redirectToRoute('home');
    }
}
