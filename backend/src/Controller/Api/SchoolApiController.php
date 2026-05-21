<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\ApiResponse;
use App\Entity\Person\School;
use App\Repository\Person\SchoolRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class SchoolApiController extends AbstractController
{
    public function __construct(
        private readonly SchoolRepository $schoolRepository,
    ) {
    }

    #[Route('/api/schools', name: 'api_schools_list', methods: ['GET'])]
    public function listSchools(): JsonResponse
    {
        $schools = $this->schoolRepository->findAllOrderedByName();

        $ret = array_map(
            static fn(School $school): ?string => $school->getName(),
            $schools,
        );

        return ApiResponse::success($ret);
    }
}
