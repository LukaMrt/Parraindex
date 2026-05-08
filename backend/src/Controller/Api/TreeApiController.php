<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\ApiResponse;
use App\Dto\Person\PersonResponseDto;
use App\Service\PersonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class TreeApiController extends AbstractController
{
    private const int PAGE_LIMIT = 20;

    public function __construct(
        private readonly PersonService $personService,
    ) {
    }

    #[Route('/api/tree', name: 'api_tree', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page   = max(1, (int) $request->query->get('page', '1'));
        $limit  = min(50, max(1, (int) $request->query->get('limit', (string) self::PAGE_LIMIT)));
        $offset = ($page - 1) * $limit;

        $people = $this->personService->getPaginated($offset, $limit);
        $total  = $this->personService->countAll();

        /** @var PersonResponseDto[] $dtos */
        $dtos = array_map(
            $this->personService->mapToResponseDto(...),
            $people,
        );

        return ApiResponse::success([
            'items' => $dtos,
            'total' => $total,
        ]);
    }
}
