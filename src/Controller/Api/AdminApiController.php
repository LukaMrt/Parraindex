<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\ApiError;
use App\Api\ApiResponse;
use App\Api\ErrorCode;
use App\Dto\Person\PersonRequestDto;
use App\Entity\Contact\Contact;
use App\Entity\Person\Person;
use App\Entity\Person\Role;
use App\Service\ContactService;
use App\Service\PersonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Role::ADMIN->value)]
final class AdminApiController extends AbstractController
{
    public function __construct(
        private readonly ContactService $contactService,
        private readonly PersonService $personService,
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

    #[Route('/api/admin/persons', name: 'api_admin_persons_create', methods: ['POST'])]
    public function createPerson(#[MapRequestPayload] PersonRequestDto $dto): JsonResponse
    {
        $existing = $this->personService->findByIdentity($dto->firstName, $dto->lastName);

        if ($existing instanceof Person) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Une personne avec ce nom existe déjà'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $person = new Person();
        $person->setFirstName($dto->firstName)
            ->setLastName($dto->lastName)
            ->setStartYear($dto->startYear)
            ->setBiography($dto->biography)
            ->setDescription($dto->description);

        if ($dto->color !== null) {
            $person->setColor($dto->color);
        }

        $this->personService->update($person);

        return ApiResponse::success($this->personService->mapToResponseDto($person), Response::HTTP_CREATED);
    }
}
