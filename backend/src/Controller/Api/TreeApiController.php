<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\ApiResponse;
use App\Dto\Person\PersonSummaryDto;
use App\Service\PersonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class TreeApiController extends AbstractController
{
    public function __construct(
        private readonly PersonService $personService,
    ) {
    }

    #[Route('/api/tree', name: 'api_tree', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $people = $this->personService->getAllWithSponsors();

        /** @var PersonSummaryDto[] $dtos */
        $dtos = array_map(
            $this->personService->mapToSummaryDto(...),
            $people,
        );

        return ApiResponse::success($dtos);
    }
}
