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

class RemoveSponsorContactExecutor extends ContactExecutor
{
    private PersonDAO $personDAO;
    private SponsorDAO $sponsorDAO;


    public function __construct(
        ContactDAO $contactDAO,
        PersonDAO  $personDAO,
        SponsorDAO $sponsorDAO,
        Redirect   $redirect
    )
    {

        $personExistsClosure = fn($value) => $this->personDAO->getPersonById($value) !== null;
        parent::__construct($contactDAO, $redirect, ContactType::REMOVE_SPONSOR, [
            new Field('senderFirstName', 'Votre prénom doit contenir au moins 1 caractère'),
            new Field('senderLastName', 'Votre nom doit contenir au moins 1 caractère'),
            new EmailField('senderEmail', 'Votre email doit être valide'),
            new NumberField('godFatherId', 'Le parrain doit être valide'),
            new CustomField('godFatherId', 'Le parrain doit exister', $personExistsClosure),
            new NumberField('godChildId', 'Le fillot doit être valide'),
            new CustomField('godChildId', 'Le fillot doit exister', $personExistsClosure),
            new Field('message', 'La description doit contenir au moins 1 caractère'),
        ]);
        $this->personDAO = $personDAO;
        $this->sponsorDAO = $sponsorDAO;
    }


    public function executeSuccess(array $data): string
    {

        $sponsor = $this->sponsorDAO->getSponsorByPeopleId($data['godFatherId'], $data['godChildId']);

        if ($sponsor === null) {
            return 'Le lien doit exister';
        }

        $contact = new SponsorContact(
            -1,
            $data['senderFirstName'] . ' ' . $data['senderLastName'],
            $data['senderEmail'],
            ContactType::REMOVE_SPONSOR,
            $data['message'] ?? '',
            $sponsor
        );

        $this->contactDAO->saveSponsorContact($contact);
        return '';
    }
}
