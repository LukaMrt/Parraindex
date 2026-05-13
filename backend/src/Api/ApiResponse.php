<?php

declare(strict_types=1);

namespace App\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Uniform JSON response envelope.
 *
 * Success: { "data": { ... } }
 * Error:   { "error": { "code": "...", "message": "...", "violations": {} } }
 */
final class ApiResponse
{
    /**
     * @param array<mixed>|object|null $data
     */
    public static function success(array|object|null $data, int $status = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse(['data' => $data], $status);
    }

    public static function error(ApiError $error, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return new JsonResponse([
            'error' => [
                'code'       => $error->code->value,
                'message'    => $error->message,
                'violations' => $error->violations,
            ],
        ], $status);
    }

    public static function notFound(ErrorCode $code, string $message): JsonResponse
    {
        return self::error(new ApiError($code, $message), Response::HTTP_NOT_FOUND);
    }

    public static function unauthorized(string $message = 'Non authentifié'): JsonResponse
    {
        return self::error(new ApiError(ErrorCode::UNAUTHORIZED, $message), Response::HTTP_UNAUTHORIZED);
    }

    public static function forbidden(string $message = 'Accès refusé'): JsonResponse
    {
        return self::error(new ApiError(ErrorCode::FORBIDDEN, $message), Response::HTTP_FORBIDDEN);
    }

    /**
     * @param array<string, string[]> $violations
     */
    public static function validationError(array $violations): JsonResponse
    {
        return self::error(
            new ApiError(ErrorCode::VALIDATION_ERROR, 'Erreur de validation', $violations),
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }
}
