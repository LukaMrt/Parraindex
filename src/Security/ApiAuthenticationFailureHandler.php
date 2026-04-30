<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use App\Api\ApiError;
use App\Api\ApiResponse;
use App\Api\ErrorCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

final class ApiAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        return ApiResponse::error(
            new ApiError(ErrorCode::UNAUTHORIZED, $exception->getMessageKey()),
            Response::HTTP_UNAUTHORIZED,
        );
    }
}
