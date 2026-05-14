<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Person\Person;
use App\Api\ApiError;
use App\Api\ApiResponse;
use App\Api\ErrorCode;
use App\Dto\Auth\MeResponseDto;
use App\Entity\Person\User;
use App\Repository\PersonRepository;
use App\Service\AuthService;
use App\Service\PersonService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class AuthApiController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly AuthService $authService,
        private readonly PersonService $personService,
        private readonly PersonRepository $personRepository,
    ) {
    }

    /**
     * Handled by json_login authenticator — this method is never reached.
     */
    #[Route('/api/auth/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('This method should not be reached — intercepted by json_login authenticator.');
    }

    /**
     * Intercepted by the api firewall logout listener — this method is never reached.
     */
    #[Route('/api/auth/logout', name: 'api_auth_logout', methods: ['POST'])]
    public function logout(): never
    {
        throw new \LogicException('This method should not be reached — intercepted by the api firewall logout listener.');
    }

    #[Route('/api/auth/me', name: 'api_auth_me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user instanceof User) {
            return ApiResponse::unauthorized();
        }

        return ApiResponse::success($this->buildMeDto($user));
    }

    #[Route('/api/auth/register', name: 'api_auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        /** @var array<string, mixed>|null $data */
        $data = json_decode((string) $request->getContent(), true);

        if (!is_array($data) || !isset($data['email'], $data['password'])) {
            return ApiResponse::validationError(['email' => ['Requis'], 'password' => ['Requis']]);
        }

        $email       = is_string($data['email']) ? $data['email'] : '';
        $password    = is_string($data['password']) ? $data['password'] : '';
        $callbackUrl = is_string($data['callbackUrl'] ?? null) ? $data['callbackUrl'] : '';
        $personId    = isset($data['personId']) && is_int($data['personId']) ? $data['personId'] : null;

        if ($personId === null && $callbackUrl === '') {
            return ApiResponse::validationError(['callbackUrl' => ['Requis']]);
        }

        $user = new User();
        $user->setEmail($email);

        try {
            $this->userService->register($user, $password, $personId);
            if ($personId === null) {
                $this->userService->sendVerificationEmail($user, $callbackUrl);
            }
        } catch (\RuntimeException $runtimeException) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, $runtimeException->getMessage()),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return ApiResponse::success(['isValidated' => $user->isValidated()], Response::HTTP_CREATED);
    }

    #[Route('/api/auth/persons-available', name: 'api_auth_persons_available', methods: ['GET'])]
    public function personsAvailable(Request $request): JsonResponse
    {
        $page    = max(1, (int) $request->query->get('page', '1'));
        $limit   = min(50, max(1, (int) $request->query->get('limit', '20')));
        $offset  = ($page - 1) * $limit;
        $persons = $this->personRepository->findWithoutUserPaginated($offset, $limit);
        $total   = $this->personRepository->countWithoutUser();

        return ApiResponse::success([
            'items' => array_map(
                static fn (Person $p): array => [
                    'id'        => $p->getId(),
                    'firstName' => $p->getFirstName(),
                    'lastName'  => $p->getLastName(),
                    'fullName'  => $p->getFirstName() . ' ' . $p->getLastName(),
                    'startYear' => $p->getStartYear(),
                ],
                $persons,
            ),
            'total' => $total,
        ]);
    }

    #[Route('/api/auth/verify-email', name: 'api_auth_verify_email', methods: ['GET'])]
    public function verifyEmail(Request $request): JsonResponse
    {
        $id   = $request->query->getInt('id');
        $user = $this->userService->findById($id);

        if (!$user instanceof User) {
            return ApiResponse::error(
                new ApiError(ErrorCode::USER_NOT_FOUND, 'Utilisateur introuvable'),
                Response::HTTP_NOT_FOUND,
            );
        }

        try {
            $this->userService->verifyEmail($request, $user);
        } catch (\Exception $exception) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, $exception->getMessage()),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return ApiResponse::success(null);
    }

    #[Route('/api/auth/resend-verification', name: 'api_auth_resend_verification', methods: ['POST'])]
    public function resendVerification(#[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        if (!$user instanceof User) {
            return ApiResponse::unauthorized();
        }

        if ($user->isValidated()) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Votre compte est déjà validé'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        /** @var array<string, mixed>|null $data */
        $data        = json_decode((string) $request->getContent(), true);
        $callbackUrl = is_array($data) && is_string($data['callbackUrl'] ?? null) ? $data['callbackUrl'] : '';

        if ($callbackUrl === '') {
            return ApiResponse::validationError(['callbackUrl' => ['Requis']]);
        }

        $this->userService->sendVerificationEmail($user, $callbackUrl);

        return ApiResponse::success(null);
    }

    #[Route('/api/auth/reset-password/request', name: 'api_auth_reset_password_request', methods: ['POST'])]
    public function resetPasswordRequest(Request $request): JsonResponse
    {
        /** @var array<string, mixed>|null $data */
        $data  = json_decode((string) $request->getContent(), true);
        $email = is_array($data) && is_string($data['email'] ?? null) ? $data['email'] : '';

        $user = $this->authService->findUserByEmail($email);

        if ($user instanceof User) {
            $this->authService->generateRandomPasswordAndNotify($user);
        }

        return ApiResponse::success(null);
    }

    private function buildMeDto(User $user): MeResponseDto
    {
        $person = $user->getPerson();

        if (!$person instanceof Person) {
            throw new \LogicException('User has no associated person.');
        }

        $loaded = $this->personService->getWithRelations($person->getId()) ?? $person;

        return new MeResponseDto(
            id: (int) $user->getId(),
            email: (string) $user->getEmail(),
            isAdmin: $user->isAdmin(),
            isValidated: $user->isValidated(),
            person: $this->personService->mapToResponseDto($loaded),
        );
    }
}
