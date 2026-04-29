<?php

declare(strict_types=1);

namespace App\Dto\Contact;

use App\Entity\Contact\Type;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ContactRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $contacterFirstName,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $contacterLastName,

        #[Assert\NotBlank]
        #[Assert\Email]
        public string $contacterEmail,

        #[Assert\NotBlank]
        public Type $type,

        #[Assert\NotBlank]
        public string $description,

        public ?string $relatedPersonFirstName = null,
        public ?string $relatedPersonLastName = null,
        public ?string $relatedPerson2FirstName = null,
        public ?string $relatedPerson2LastName = null,
        public ?int $entryYear = null,
        public ?string $sponsorType = null,
        public ?string $sponsorDate = null,
        public ?string $registrationToken = null,
    ) {
    }
}
