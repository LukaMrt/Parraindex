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
        return new SponsorResponseDto(
            id: (int) $sponsor->getId(),
            godFatherId: $sponsor->getGodFather()->getId(),
            godFatherName: $sponsor->getGodFather()->getFullName(),
            godChildId: $sponsor->getGodChild()->getId(),
            godChildName: $sponsor->getGodChild()->getFullName(),
            type: $sponsor->getType()?->getTitle() ?? '',
            date: $sponsor->getDate()?->format('Y-m-d'),
            description: $sponsor->getDescription(),
        );
    }
}
