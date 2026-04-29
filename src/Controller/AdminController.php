<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Type;
use App\Entity\Person\Role;
use App\Service\ContactService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Role::ADMIN->value)]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly ContactService $contactService,
    ) {
    }

    #[Route('/admin/contact', name: 'admin_contact', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        return $this->render('contactAdmin.html.twig', [
            'contacts'    => $this->contactService->getAll(),
            'typeContact' => Type::allTitles(),
        ]);
    }

    #[Route('/admin/contact/{id}/delete', name: 'admin_contact_delete', methods: [Request::METHOD_POST])]
    #[IsCsrfTokenValid('delete_contact', tokenKey: '_token')]
    public function delete(Contact $contact): Response
    {
        $this->contactService->close($contact);
        $this->addFlash('success', 'Contact cloturé');

        return $this->redirectToRoute('admin_contact');
    }

    #[Route('/admin/contact/{id}/resolve', name: 'admin_contact_resolve', methods: [Request::METHOD_POST])]
    #[IsCsrfTokenValid('resolve_contact', tokenKey: '_token')]
    public function resolve(Contact $contact): Response
    {
        $response = $this->contactService->resolve($contact);

        if ($response instanceof Response) {
            return $response;
        }

        $this->contactService->close($contact);
        $this->addFlash('success', 'Contact résolu');

        return $this->redirectToRoute('admin_contact');
    }
}
