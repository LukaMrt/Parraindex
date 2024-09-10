<?php

namespace App\Controller;

use App\Application\contact\ContactService;
use App\Entity\Contact\Type;
use App\Entity\Person\Role;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactAdminController extends AbstractController
{
    public function __construct(
        private readonly ContactService $contactService
    ) {
    }

    #[Route('/admin/contact', name: 'contactAdmin')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);

        $list = $this->contactService->getContactList();
        $types = Type::getValues();

        return $this->render('contactAdmin.html.twig', [
            'contacts' => $list,
            'typeContact' => $types
        ]);
    }
}
