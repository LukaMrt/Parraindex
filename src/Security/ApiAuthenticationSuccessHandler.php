<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use App\Api\ApiResponse;
use App\Dto\Auth\MeResponseDto;
use App\Dto\Person\PersonSummaryDto;
use App\Entity\Person\Person;
use App\Entity\Person\User;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final readonly class ApiAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return ApiResponse::unauthorized();
        }

        $person = $user->getPerson();

        if (!$person instanceof Person) {
            return ApiResponse::unauthorized('Profil introuvable');
        }

        $personDto = $this->objectMapper->map($person, PersonSummaryDto::class);

        $dto = new MeResponseDto(
            id: (int) $user->getId(),
            email: (string) $user->getEmail(),
            isAdmin: $user->isAdmin(),
            isVerified: $user->isVerified(),
            person: $personDto,
        );

        $response = ApiResponse::success($dto);

        $xsrfToken = bin2hex(random_bytes(32));
        $response->headers->setCookie(new Cookie(
            name: 'XSRF-TOKEN',
            value: $xsrfToken,
            httpOnly: false,
            sameSite: 'lax',
        ));
        $request->getSession()->set('_xsrf_token', $xsrfToken);

        return $response;
    }
}
