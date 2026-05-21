<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\ApiResponse;
use App\Entity\Person\Association;
use App\Repository\Person\AssociationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class AssociationApiController extends AbstractController
{
    public function __construct(
        private readonly AssociationRepository $associationRepository,
    ) {
    }

    #[Route('/api/associations', name: 'api_associations_list', methods: ['GET'])]
    public function listAssociations(): JsonResponse
    {
        $associations = $this->associationRepository->findAllOrderedByName();

        $ret = array_map(
            static fn(Association $association): ?string => $association->getName(),
            $associations,
        );

        return ApiResponse::success($ret);
    }
}
