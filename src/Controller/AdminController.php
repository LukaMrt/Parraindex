<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\contact\ContactService;
use App\Entity\Contact\Contact;
use App\Entity\Contact\Type;
use App\Entity\Person\Role;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted(Role::ADMIN->value)]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly ContactRepository $contactRepository,
    ) {
    }

    #[Route('/contact', name: 'admin_contact', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        $list  = $this->contactRepository->getAll();
        $types = Type::allTitles();

        return $this->render('contactAdmin.html.twig', [
            'contacts'    => $list,
            'typeContact' => $types
        ]);
    }

    #[Route('/contact/{id}/resolve', name: 'admin_contact_resolve', methods: [Request::METHOD_GET])]
    public function resolve(Contact $contact): Response
    {
        $contact->setResolutionDate(new \DateTime());
        $this->contactRepository->update($contact);

        $this->addFlash('success', 'Contact résolu');
        return $this->redirectToRoute('admin_contact');
    }

    #[Route('/contact/{id}/delete', name: 'admin_contact_delete', methods: [Request::METHOD_GET])]
    public function delete(Contact $contact): Response
    {
        $contact->setResolutionDate(new \DateTime());
        $this->contactRepository->update($contact);

        $this->addFlash('success', 'Contact cloturé');
        return $this->redirectToRoute('admin_contact');
    }
}
