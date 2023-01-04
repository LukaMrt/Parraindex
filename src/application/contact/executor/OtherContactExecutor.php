<?php

namespace App\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\field\EmailField;
use App\application\contact\field\Field;
use App\application\redirect\Redirect;
use App\model\contact\ContactType;

/**
 * Contact executor for the adding of a global message
 */
class OtherContactExecutor extends SimpleContactExecutor
{
    /**
     * @param ContactDAO $contactDAO DAO for contacts
     * @param Redirect $redirect Redirect service
     */
    public function __construct(ContactDAO $contactDAO, Redirect $redirect)
    {
        parent::__construct($contactDAO, $redirect, ContactType::OTHER, [
            new Field('senderFirstName', 'Votre prénom doit contenir au moins 1 caractère'),
            new Field('senderLastName', 'Votre nom doit contenir au moins 1 caractère'),
            new EmailField('senderEmail', 'Votre email doit être valide'),
            new Field('message', 'La description doit contenir au moins 1 caractère'),
        ]);
    }
}
