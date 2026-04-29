<?php

declare(strict_types=1);

namespace App\Api;

final readonly class ApiError
{
    /**
     * @param array<string, string[]> $violations field name → list of error messages
     */
    public function __construct(
        public ErrorCode $code,
        public string $message,
        public array $violations = [],
    ) {
    }
}
