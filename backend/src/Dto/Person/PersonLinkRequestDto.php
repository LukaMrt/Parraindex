<?php

declare(strict_types=1);

namespace App\Dto\Person;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PersonLinkRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $title,
        #[Assert\NotBlank]
        #[Assert\Url]
        #[Assert\Length(max: 2048)]
        public string $url,
    ) {
    }
}
