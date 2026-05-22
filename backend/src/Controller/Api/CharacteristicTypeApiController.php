<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Characteristic\CharacteristicType;
use App\Api\ApiResponse;
use App\Repository\CharacteristicTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class CharacteristicTypeApiController extends AbstractController
{
    public function __construct(
        private readonly CharacteristicTypeRepository $characteristicTypeRepository,
    ) {
    }

    #[Route('/api/characteristic-types', name: 'api_characteristic_types_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $types = $this->characteristicTypeRepository->getAll();

        return ApiResponse::success(array_map(
            static fn(CharacteristicType $type): array => [
                'id'    => $type->getId(),
                'title' => $type->getTitle(),
                'url'   => $type->getUrl(),
                'image' => $type->getImage(),
            ],
            $types,
        ));
    }
}
