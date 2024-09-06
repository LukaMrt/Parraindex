<?php

namespace App\Entity\contact;

/**
 * Default contact class
 */
class DefaultContact extends Contact
{
    /**
     * @return array[] Description of the contact
     */
    public function getDescription(): array
    {
        return [];
    }
}
