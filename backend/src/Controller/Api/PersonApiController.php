<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\Image;
use App\Dto\Person\PersonResponseDto;
use App\Api\ApiError;
use App\Api\ApiResponse;
use App\Api\ErrorCode;
use App\Dto\Person\PersonRequestDto;
use App\Entity\Person\Person;
use App\Entity\Person\Role;
use App\Entity\Person\User;
use App\Security\Voter\PersonVoter;
use App\Service\PersonService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PersonApiController extends AbstractController
{
    public function __construct(
        private readonly PersonService $personService,
        private readonly UserService $userService,
        private readonly ValidatorInterface $validator,
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

        /** @var PersonResponseDto[] $dtos */
        $dtos = array_map(
            $this->personService->mapToResponseDto(...),
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

        $this->personService->update($person);

        return ApiResponse::success($this->personService->mapToResponseDto($person));
    }

    #[Route('/api/persons/{id}', name: 'api_persons_delete', methods: ['DELETE'])]
    #[IsGranted(Role::ADMIN->value)]
    public function delete(Person $person): JsonResponse
    {
        $this->userService->deleteByPersonId($person->getId());
        $this->personService->delete($person);

        return ApiResponse::success(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/persons/{id}/picture', name: 'api_persons_picture', methods: ['POST'])]
    #[IsGranted(PersonVoter::EDIT, subject: 'person')]
    public function uploadPicture(Person $person, Request $request): JsonResponse
    {
        $file = $request->files->get('picture');

        if (!$file instanceof UploadedFile) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Aucun fichier envoyé'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $person->setPictureFile($file);

        $errors = $this->validator->validate($person->getPictureFile(), [
            new Image(
                maxSize: '5M',
                mimeTypes: [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'image/gif',
                ],
                maxWidth: 4096,
                maxHeight: 4096,
                detectCorrupted: true,
            ),
        ]);

        if (count($errors) > 0) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, (string) $errors->get(0)->getMessage()),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $this->personService->update($person);
        $person->setPictureFile(null);

        return ApiResponse::success(['picture' => $person->getPicture()]);
    }

    #[Route('/api/persons/{id}/account', name: 'api_persons_get_account', methods: ['GET'])]
    public function getAccount(Person $person, #[CurrentUser] ?User $currentUser): JsonResponse
    {
        if (!$currentUser instanceof User) {
            return ApiResponse::unauthorized();
        }

        $isOwnProfile = $currentUser->getPerson()?->getId() === $person->getId();

        if (!$isOwnProfile && !$currentUser->isAdmin()) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Accès refusé'),
                Response::HTTP_FORBIDDEN,
            );
        }

        $targetUser = $this->userService->findByPersonId($person->getId());

        if (!$targetUser instanceof User) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Aucun compte associé à cette personne'),
                Response::HTTP_NOT_FOUND,
            );
        }

        return ApiResponse::success(['email' => $targetUser->getEmail()]);
    }

    #[Route('/api/persons/{id}/account', name: 'api_persons_update_account', methods: ['PATCH'])]
    public function updateAccount(Person $person, Request $request, #[CurrentUser] ?User $currentUser): JsonResponse
    {
        if (!$currentUser instanceof User) {
            return ApiResponse::unauthorized();
        }

        $isOwnProfile = $currentUser->getPerson()?->getId() === $person->getId();
        $isAdmin      = $currentUser->isAdmin();

        if (!$isOwnProfile && !$isAdmin) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Accès refusé'),
                Response::HTTP_FORBIDDEN,
            );
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode((string) $request->getContent(), true);

        if (!is_array($data)) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Corps de requête invalide'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $targetUser = $this->userService->findByPersonId($person->getId());

        if (!$targetUser instanceof User) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Aucun compte associé à cette personne'),
                Response::HTTP_NOT_FOUND,
            );
        }

        $newEmail        = isset($data['email']) && is_string($data['email']) ? $data['email'] : null;
        $currentPassword = isset($data['currentPassword']) && is_string($data['currentPassword']) ? $data['currentPassword'] : null;
        $newPassword     = isset($data['newPassword']) && is_string($data['newPassword']) ? $data['newPassword'] : null;

        // Admins editing another user's account don't need the current password
        $skipPasswordCheck = $isAdmin && !$isOwnProfile;

        try {
            $this->userService->updateCredentials($targetUser, $newEmail, $currentPassword, $newPassword, $skipPasswordCheck);
        } catch (\RuntimeException $runtimeException) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, $runtimeException->getMessage()),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return ApiResponse::success(null);
    }

    #[Route('/api/persons/batch', name: 'api_persons_batch', methods: ['POST'])]
    public function batch(Request $request): JsonResponse
    {
        /** @var array<string, mixed>|null $body */
        $body   = json_decode((string) $request->getContent(), true);
        $rawIds = isset($body['ids']) && is_array($body['ids']) ? $body['ids'] : [];
        /** @var int[] $ids */
        $ids = array_filter(array_map(static fn(mixed $v): int|false => is_numeric($v) ? (int) $v : false, $rawIds));

        if ($ids === []) {
            return ApiResponse::success([]);
        }

        $persons = $this->personService->getByIds($ids);

        return ApiResponse::success(array_map($this->personService->mapToResponseDto(...), $persons));
    }

    #[Route('/api/persons/{id}/export', name: 'api_persons_export', methods: ['GET'])]
    #[IsGranted(PersonVoter::DOWNLOAD_DATA, subject: 'person')]
    public function export(Person $person): JsonResponse
    {
        return ApiResponse::success($this->personService->mapToResponseDto($person));
    }
}
