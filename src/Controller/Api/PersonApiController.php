<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Dto\Person\PersonSummaryDto;
use App\Api\ApiError;
use App\Api\ApiResponse;
use App\Api\ErrorCode;
use App\Dto\Person\PersonRequestDto;
use App\Entity\Person\Person;
use App\Entity\Person\Role;
use App\Security\Voter\PersonVoter;
use App\Service\PersonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PersonApiController extends AbstractController
{
    public function __construct(
        private readonly PersonService $personService,
    ) {
    }

    #[Route('/api/persons', name: 'api_persons_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $allowedOrderBy = [
            'id',
            'firstName',
            'lastName',
            'startYear',
            'createdAt',
        ];
        $orderByParam   = $request->query->getString('orderBy', 'id');
        $orderBy        = in_array($orderByParam, $allowedOrderBy, true) ? $orderByParam : 'id';
        $people = $this->personService->getAll($orderBy);

        /** @var PersonSummaryDto[] $dtos */
        $dtos = array_map(
            $this->personService->mapToSummaryDto(...),
            $people,
        );

        return ApiResponse::success($dtos);
    }

    #[Route('/api/persons/{id}', name: 'api_persons_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $person = $this->personService->getWithRelations($id);

        if (!$person instanceof Person) {
            return ApiResponse::notFound(ErrorCode::PERSON_NOT_FOUND, 'La personne demandée n\'existe pas');
        }

        return ApiResponse::success($this->personService->mapToResponseDto($person));
    }

    #[Route('/api/persons/{id}', name: 'api_persons_update', methods: ['PUT'])]
    #[IsGranted(PersonVoter::EDIT, subject: 'person')]
    public function update(
        Person $person,
        #[MapRequestPayload] PersonRequestDto $dto,
    ): JsonResponse {
        $person->setFirstName($dto->firstName)
            ->setLastName($dto->lastName)
            ->setStartYear($dto->startYear)
            ->setBiography($dto->biography)
            ->setDescription($dto->description);

        if ($dto->color !== null) {
            $person->setColor($dto->color);
        }

        $this->personService->update($person);

        return ApiResponse::success($this->personService->mapToResponseDto($person));
    }

    #[Route('/api/persons/{id}', name: 'api_persons_delete', methods: ['DELETE'])]
    #[IsGranted(Role::ADMIN->value)]
    public function delete(Person $person): JsonResponse
    {
        $this->personService->delete($person);

        return ApiResponse::success(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/persons/{id}/picture', name: 'api_persons_picture', methods: ['POST'])]
    #[IsGranted(PersonVoter::EDIT, subject: 'person')]
    public function uploadPicture(Person $person, Request $request): JsonResponse
    {
        $file = $request->files->get('picture');

        if ($file === null) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Aucun fichier envoyé'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        if (!$file instanceof UploadedFile) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Fichier invalide'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $extension = $file->guessExtension() ?? 'bin';
        $filename  = sprintf('%d_%s.%s', $person->getId(), uniqid(), $extension);
        /** @var string $projectDir */
        $projectDir = $this->getParameter('kernel.project_dir');
        $file->move($projectDir . '/public/uploads/pictures', $filename);

        $person->setPicture($filename);
        $this->personService->update($person);

        return ApiResponse::success(['picture' => $filename]);
    }

    #[Route('/api/persons/{id}/export', name: 'api_persons_export', methods: ['GET'])]
    #[IsGranted(PersonVoter::DOWNLOAD_DATA, subject: 'person')]
    public function export(Person $person): JsonResponse
    {
        return ApiResponse::success($this->personService->mapToResponseDto($person));
    }
}
