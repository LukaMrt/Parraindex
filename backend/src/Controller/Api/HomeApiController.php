<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\ApiResponse;
use App\Dto\HomeStatsDto;
use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HomeApiController extends AbstractController
{
    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {
    }

    #[Route('/api/home-stats', name: 'api_home_stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        $promoGroups  = $this->personRepository->countByStartYear();
        $totalPersons = array_sum(array_column($promoGroups, 'count'));

        $dto = new HomeStatsDto(
            totalPersons: $totalPersons,
            totalPromos: count($promoGroups),
            promoGroups: $promoGroups,
        );

        return ApiResponse::success($dto);
    }
}
