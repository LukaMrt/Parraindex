<?php

namespace App\application\contact\executor;

use App\model\contact\DefaultContact;

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
            $data['senderFirstName'] . ' ' . $data['senderLastName'],
            $data['senderEmail'],
            $this->contactType,
            $data['message'],
        );

        $this->contactDAO->saveSimpleContact($contact);
        return '';
    }
}
