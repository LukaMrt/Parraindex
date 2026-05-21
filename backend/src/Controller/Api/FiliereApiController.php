<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\ApiResponse;
use App\Entity\Person\Filiere;
use App\Repository\Person\FiliereRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class FiliereApiController extends AbstractController
{
    public function __construct(
        private readonly FiliereRepository $filiereRepository,
    ) {
    }

    #[Route('/api/filieres', name: 'api_filieres_list', methods: ['GET'])]
    public function listFilieres(): JsonResponse
    {
        $filieres = $this->filiereRepository->findAllOrderedByName();

        $ret = array_map(
            static fn(Filiere $filiere): ?string => $filiere->getName(),
            $filieres,
        );

        return ApiResponse::success($ret);
    }
}
