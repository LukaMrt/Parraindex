<?php

declare(strict_types=1);

namespace App\Service;

use Liip\ImagineBundle\Service\FilterService;

final readonly class AvatarCacheService
{
    private const array FILTERS = [
        'avatar_thumb',
        'avatar_full',
    ];

    public function __construct(private FilterService $filterService)
    {
    }

    public function warmUp(string $filename): void
    {
        $path = 'uploads/avatars/' . $filename;

        foreach (self::FILTERS as $filter) {
            $this->filterService->warmUpCache($path, $filter, null, true);
        }
    }

    public function bust(string $filename): void
    {
        $path = 'uploads/avatars/' . $filename;

        foreach (self::FILTERS as $filter) {
            $this->filterService->bustCache($path, $filter);
        }
    }
}
