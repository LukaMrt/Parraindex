<?php

namespace App\Application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\field\EmailField;
use App\Application\contact\field\Field;
use App\Application\redirect\Redirect;
use App\Entity\ContactType;

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
