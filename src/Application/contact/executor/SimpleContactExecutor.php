<?php

declare(strict_types=1);

namespace App\Application\contact\executor;

use App\Entity\old\contact\DefaultContact;

/**
 * Contact executor for adding a default contact (bug, other subject...)
 */
abstract class SimpleContactExecutor extends ContactExecutor
{
    /**
     * Performs the actions when the contact is valid
     * @param array $data Data from the form
     */
    #[\Override]
    public function executeSuccess(array $data): string
    {
        $defaultContact = new DefaultContact(
            -1,
            date('Y-m-d'),
            null,
            $data['senderFirstName'] . ' ' . $data['senderLastName'],
            $data['senderEmail'],
            $this->contactType,
            $data['message'],
        );

        $this->contactDAO->saveSimpleContact($defaultContact);
        return '';
    }
}
