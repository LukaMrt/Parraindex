<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Sponsor\SponsorResponseDto;
use App\Entity\Sponsor\Sponsor;
use App\Repository\SponsorRepository;

final readonly class SponsorService
{
    public function __construct(
        private SponsorRepository $sponsorRepository,
    ) {
    }

    public function getById(int $id): ?Sponsor
    {
        return $this->sponsorRepository->getById($id);
    }

    public function update(Sponsor $sponsor): void
    {
        $this->sponsorRepository->update($sponsor);
    }

    public function delete(Sponsor $sponsor): void
    {
        $this->sponsorRepository->delete($sponsor);
    }

    public function mapToResponseDto(Sponsor $sponsor): SponsorResponseDto
    {
        $godFather = $sponsor->getGodFather() ?? throw new \LogicException('Sponsor has no godFather.');
        $godChild  = $sponsor->getGodChild() ?? throw new \LogicException('Sponsor has no godChild.');

        return new SponsorResponseDto(
            id: (int) $sponsor->getId(),
            godFatherId: $godFather->getId(),
            godFatherName: $godFather->getFullName(),
            godChildId: $godChild->getId(),
            godChildName: $godChild->getFullName(),
            type: $sponsor->getType()->name ?? '',
            date: $sponsor->getDate()?->format('Y-m-d'),
            description: $sponsor->getDescription(),
        );
    }
}
