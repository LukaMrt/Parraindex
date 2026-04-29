<?php

declare(strict_types=1);

namespace App\Dto\Contact;

use App\Entity\Contact\Type;
use Symfony\Component\Validator\Constraints as Assert;

final class ContactRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public readonly string $contacterFirstName,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public readonly string $contacterLastName,

        #[Assert\NotBlank]
        #[Assert\Email]
        public readonly string $contacterEmail,

        #[Assert\NotBlank]
        public readonly Type $type,

        #[Assert\NotBlank]
        public readonly string $description,

        public readonly ?string $relatedPersonFirstName = null,
        public readonly ?string $relatedPersonLastName = null,
        public readonly ?string $relatedPerson2FirstName = null,
        public readonly ?string $relatedPerson2LastName = null,
        public readonly ?int $entryYear = null,
        public readonly ?string $sponsorType = null,
        public readonly ?string $sponsorDate = null,
        public readonly ?string $registrationToken = null,
    ) {
    }
}
