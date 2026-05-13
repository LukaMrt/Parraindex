<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\ApiError;
use App\Api\ApiResponse;
use App\Api\ErrorCode;
use App\Entity\Contact\Contact;
use App\Entity\Person\Role;
use App\Service\ContactService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Role::ADMIN->value)]
final class AdminApiController extends AbstractController
{
    public function __construct(
        private readonly ContactService $contactService,
    ) {
    }

    #[Route('/api/admin/contacts', name: 'api_admin_contacts_list', methods: ['GET'])]
    public function listContacts(): JsonResponse
    {
        $contacts = $this->contactService->getAll();

        return ApiResponse::success(array_map(
            static fn (Contact $c): array => [
                'id'                   => $c->getId(),
                'contacterFirstName'   => $c->getContacterFirstName(),
                'contacterLastName'    => $c->getContacterLastName(),
                'contacterEmail'       => $c->getContacterEmail(),
                'type'                 => $c->getType()?->value,
                'description'          => $c->getDescription(),
                'createdAt'            => $c->getCreatedAt()?->format('Y-m-d H:i:s'),
                'resolutionDate'       => $c->getResolutionDate()?->format('Y-m-d H:i:s'),
                'relatedPersonFirstName' => $c->getRelatedPersonFirstName(),
                'relatedPersonLastName'  => $c->getRelatedPersonLastName(),
            ],
            $contacts,
        ));
    }

    #[Route('/api/admin/contacts/{id}', name: 'api_admin_contacts_resolve', methods: ['PUT'])]
    public function resolveContact(Contact $contact): JsonResponse
    {
        if ($contact->getResolutionDate() instanceof \DateTimeInterface) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Cette demande est déjà traitée'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $this->contactService->resolve($contact);
        $this->contactService->close($contact);

        return ApiResponse::success(null);
    }
}
