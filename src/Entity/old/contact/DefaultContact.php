<?php

declare(strict_types=1);

namespace App\Entity\old\contact;

/**
 * Default contact class
 */
class DefaultContact extends Contact
{
    /**
     * @return array[] Description of the contact
     */
    #[\Override]
    public function getDescription(): array
    {
        return [];
    }
}
