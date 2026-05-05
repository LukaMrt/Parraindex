<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Person\Person;
use App\Api\ApiError;
use App\Api\ApiResponse;
use App\Api\ErrorCode;
use App\Dto\Auth\MeResponseDto;
use App\Dto\Person\PersonSummaryDto;
use App\Entity\Person\User;
use App\Service\AuthService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class AuthApiController extends AbstractController
{
    public function __construct(
        private readonly ObjectMapperInterface $objectMapper,
        private readonly UserService $userService,
        private readonly AuthService $authService,
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

    #[Route('/api/auth/logout', name: 'api_auth_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $request->getSession()->invalidate();

        return ApiResponse::success(null);
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

        $email    = is_string($data['email']) ? $data['email'] : '';
        $password = is_string($data['password']) ? $data['password'] : '';

        $user = new User();
        $user->setEmail($email);

        try {
            $this->userService->register($user, $password);
            $this->userService->sendVerificationEmail($user);
        } catch (\RuntimeException $runtimeException) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, $runtimeException->getMessage()),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return ApiResponse::success(null, Response::HTTP_CREATED);
    }

    #[Route('/api/auth/verify-email', name: 'api_auth_verify_email', methods: ['GET'])]
    public function verifyEmail(Request $request): JsonResponse
    {
        $id = $request->query->getInt('id');
        $user = $this->userService->findById($id);

        if (!$user instanceof User) {
            return ApiResponse::error(
                new ApiError(ErrorCode::NOT_FOUND, 'Utilisateur introuvable'),
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

    #[Route('/api/auth/reset-password/request', name: 'api_auth_reset_password_request', methods: ['POST'])]
    public function resetPasswordRequest(Request $request): JsonResponse
    {
        /** @var array<string, mixed>|null $data */
        $data  = json_decode((string) $request->getContent(), true);
        $email = is_array($data) && is_string($data['email'] ?? null) ? $data['email'] : '';

        $user = $this->authService->findUserByEmail($email);

        if ($user instanceof User) {
            $this->authService->generateAndSendResetToken($user);
        }

        return ApiResponse::success(null);
    }

    #[Route('/api/auth/reset-password/confirm', name: 'api_auth_reset_password_confirm', methods: ['POST'])]
    public function resetPasswordConfirm(Request $request): JsonResponse
    {
        /** @var array<string, mixed>|null $data */
        $data     = json_decode((string) $request->getContent(), true);
        $token    = is_array($data) && is_string($data['token'] ?? null) ? $data['token'] : '';
        $password = is_array($data) && is_string($data['password'] ?? null) ? $data['password'] : '';

        try {
            $user = $this->authService->validateTokenAndFetchUser($token);
        } catch (\Exception $exception) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, $exception->getMessage()),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $this->authService->removeResetRequest($token);
        $this->authService->resetPassword($user, $password);

        return ApiResponse::success(null);
    }

    private function buildMeDto(User $user): MeResponseDto
    {
        $person = $user->getPerson();

        if (!$person instanceof Person) {
            throw new \LogicException('User has no associated person.');
        }

        $personDto = $this->objectMapper->map($person, PersonSummaryDto::class);

        return new MeResponseDto(
            id: (int) $user->getId(),
            email: (string) $user->getEmail(),
            isAdmin: $user->isAdmin(),
            isVerified: $user->isVerified(),
            person: $personDto,
        );
    }
}
