<?php

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
     * @return string
     */
    public function executeSuccess(array $data): string
    {
        $contact = new DefaultContact(
            -1,
            date('Y-m-d'),
            null,
            $data['senderFirstName'] . ' ' . $data['senderLastName'],
            $data['senderEmail'],
            $this->contactType,
            $data['message'],
        );

        $this->contactDAO->saveSimpleContact($contact);
        return '';
    }
}
