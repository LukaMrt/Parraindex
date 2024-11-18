<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\contact\ContactService;
use App\Entity\Contact\Type;
use App\Entity\Person\Role;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted(Role::ADMIN->value)]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly ContactService $contactService
    ) {
    }

    #[Route('/contact', name: 'admin_contact')]
    public function index(): Response
    {
        $list  = $this->contactService->getContactList();
        $types = Type::getValues();

        return $this->render('contactAdmin.html.twig', [
            'contacts' => $list,
            'typeContact' => $types
        ]);
    }
}
