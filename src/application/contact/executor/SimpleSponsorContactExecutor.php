<?php

namespace App\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\field\CustomField;
use App\application\contact\field\EmailField;
use App\application\contact\field\Field;
use App\application\contact\field\NumberField;
use App\application\person\PersonDAO;
use App\application\redirect\Redirect;
use App\application\sponsor\SponsorDAO;
use App\model\contact\ContactType;
use App\model\contact\SponsorContact;

/**
 * Contact executor for the adding of a default contact (bug, other subject...)
 */
abstract class SimpleSponsorContactExecutor extends ContactExecutor
{
    /**
     * @var SponsorDAO $sponsorDAO DAO for sponsors
     */
    private SponsorDAO $sponsorDAO;


    /**
     * @param ContactDAO $contactDAO DAO for contacts
     * @param Redirect $redirect Redirect service
     * @param ContactType $contactType Contact type
     * @param SponsorDAO $sponsorDAO DAO for sponsors
     * @param PersonDAO $personDAO DAO for persons
     */
    public function __construct(
        ContactDAO $contactDAO,
        Redirect $redirect,
        ContactType $contactType,
        SponsorDAO $sponsorDAO,
        PersonDAO $personDAO
    ) {
        $personExistsClosure = fn($value) => $personDAO->getPersonById($value) !== null;
        parent::__construct($contactDAO, $redirect, $contactType, [
            new Field('senderFirstName', 'Votre prénom doit contenir au moins 1 caractère'),
            new Field('senderLastName', 'Votre nom doit contenir au moins 1 caractère'),
            new EmailField('senderEmail', 'Votre email doit être valide'),
            new NumberField('godFatherId', 'Le parrain doit être valide'),
            new CustomField('godFatherId', 'Le parrain doit exister', $personExistsClosure),
            new NumberField('godChildId', 'Le fillot doit être valide'),
            new CustomField('godChildId', 'Le fillot doit exister', $personExistsClosure),
            new Field('message', 'La description doit contenir au moins 1 caractère'),
        ]);
        $this->sponsorDAO = $sponsorDAO;
    }


    /**
     * Performs the actions when the contact is valid
     * @param array $data Data from the form
     * @return string error message or empty string if no error
     */
    public function executeSuccess(array $data): string
    {

        $sponsor = $this->sponsorDAO->getSponsorByPeopleId($data['godFatherId'], $data['godChildId']);

        if ($sponsor === null) {
            return 'Le lien doit exister';
        }

        $contact = new SponsorContact(
            -1,
            date('Y-m-d'),
            null,
            $data['senderFirstName'] . ' ' . $data['senderLastName'],
            $data['senderEmail'],
            $this->contactType,
            $data['message'] ?? '',
            $sponsor
        );

        $this->contactDAO->saveSponsorContact($contact);
        return '';
    }
}
