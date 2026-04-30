<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\ApiResponse;
use App\Api\ErrorCode;
use App\Dto\Sponsor\SponsorRequestDto;
use App\Entity\Person\Person;
use App\Entity\Sponsor\Sponsor;
use App\Security\Voter\SponsorVoter;
use App\Service\PersonService;
use App\Service\SponsorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class SponsorApiController extends AbstractController
{
    public function __construct(
        private readonly SponsorService $sponsorService,
        private readonly PersonService $personService,
    ) {
    }

    #[Route('/api/sponsors/{id}', name: 'api_sponsors_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $sponsor = $this->sponsorService->getById($id);

        if (!$sponsor instanceof Sponsor) {
            return ApiResponse::notFound(ErrorCode::SPONSOR_NOT_FOUND, 'Le parrainage demandé n\'existe pas');
        }

        return ApiResponse::success($this->sponsorService->mapToResponseDto($sponsor));
    }

    #[Route('/api/sponsors', name: 'api_sponsors_create', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(#[MapRequestPayload] SponsorRequestDto $dto): JsonResponse
    {
        $godFather = $this->personService->getById($dto->godFatherId);
        $godChild  = $this->personService->getById($dto->godChildId);

        if (!$godFather instanceof Person) {
            return ApiResponse::notFound(ErrorCode::PERSON_NOT_FOUND, 'Le parrain demandé n\'existe pas');
        }

        if (!$godChild instanceof Person) {
            return ApiResponse::notFound(ErrorCode::PERSON_NOT_FOUND, 'Le filleul demandé n\'existe pas');
        }

        $sponsor = new Sponsor();
        $sponsor->setGodFather($godFather)
            ->setGodChild($godChild)
            ->setType($dto->type)
            ->setDescription($dto->description);

        if ($dto->date !== null) {
            $sponsor->setDate(new \DateTime($dto->date));
        }

        $this->sponsorService->update($sponsor);

        return ApiResponse::success($this->sponsorService->mapToResponseDto($sponsor), Response::HTTP_CREATED);
    }

    #[Route('/api/sponsors/{id}', name: 'api_sponsors_update', methods: ['PUT'])]
    #[IsGranted(SponsorVoter::EDIT, subject: 'sponsor')]
    public function update(
        Sponsor $sponsor,
        #[MapRequestPayload] SponsorRequestDto $dto,
    ): JsonResponse {
        $godFather = $this->personService->getById($dto->godFatherId);
        $godChild  = $this->personService->getById($dto->godChildId);

        if (!$godFather instanceof Person) {
            return ApiResponse::notFound(ErrorCode::PERSON_NOT_FOUND, 'Le parrain demandé n\'existe pas');
        }

        if (!$godChild instanceof Person) {
            return ApiResponse::notFound(ErrorCode::PERSON_NOT_FOUND, 'Le filleul demandé n\'existe pas');
        }

        $sponsor->setGodFather($godFather)
            ->setGodChild($godChild)
            ->setType($dto->type)
            ->setDescription($dto->description);

        if ($dto->date !== null) {
            $sponsor->setDate(new \DateTime($dto->date));
        }

        $this->sponsorService->update($sponsor);

        return ApiResponse::success($this->sponsorService->mapToResponseDto($sponsor));
    }

    #[Route('/api/sponsors/{id}', name: 'api_sponsors_delete', methods: ['DELETE'])]
    #[IsGranted(SponsorVoter::EDIT, subject: 'sponsor')]
    public function delete(Sponsor $sponsor): JsonResponse
    {
        $this->sponsorService->delete($sponsor);

        return ApiResponse::success(null, Response::HTTP_NO_CONTENT);
    }
}
