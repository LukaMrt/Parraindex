<?php

namespace App\model\contact;

class DefaultContact extends Contact
{
    public function __construct(int $id, string $contacterName, string $contacterEmail, ContactType $type, string $description)
    {
        parent::__construct($id, $contacterName, $contacterEmail, $type, $description);
    }

    public function getDescription(): array
    {
        return [];
    }
}
