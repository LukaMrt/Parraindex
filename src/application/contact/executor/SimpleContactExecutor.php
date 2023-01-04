<?php

namespace App\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\redirect\Redirect;
use App\model\contact\ContactType;
use App\model\contact\DefaultContact;

/**
 * Contact executor for adding a default contact (bug, other subject...)
 */
abstract class SimpleContactExecutor extends ContactExecutor
{
    /**
     * @param ContactDAO $contactDAO DAO for contacts
     * @param Redirect $redirect Redirect service
     * @param ContactType $contactType Contact type
     * @param array $fields Fields to check
     */
    public function __construct(ContactDAO $contactDAO, Redirect $redirect, ContactType $contactType, array $fields)
    {
        parent::__construct($contactDAO, $redirect, $contactType, $fields);
    }


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
