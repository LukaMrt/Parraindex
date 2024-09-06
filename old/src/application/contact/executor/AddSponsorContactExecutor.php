<?php

namespace App\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\field\BoundedNumberField;
use App\application\contact\field\CustomField;
use App\application\contact\field\DateField;
use App\application\contact\field\EmailField;
use App\application\contact\field\Field;
use App\application\contact\field\NumberField;
use App\application\person\PersonDAO;
use App\application\redirect\Redirect;
use App\application\sponsor\SponsorDAO;
use App\model\contact\ContactType;
use App\model\contact\SponsorContact;
use App\model\sponsor\SponsorFactory;

/**
 * Contact executor for the adding of a sponsor
 */
class AddSponsorContactExecutor extends ContactExecutor
{
    /**
     * @var PersonDAO $personDAO DAO for persons
     */
    private PersonDAO $personDAO;

    /**
     * @var SponsorDAO $sponsorDAO DAO for sponsors
     */
    private SponsorDAO $sponsorDAO;


    /**
     * @param ContactDAO $contactDAO DAO for contacts
     * @param Redirect $redirect Redirect object
     * @param PersonDAO $personDAO DAO for persons
     * @param SponsorDAO $sponsorDAO DAO for sponsors
     */
    public function __construct(
        ContactDAO $contactDAO,
        Redirect $redirect,
        PersonDAO $personDAO,
        SponsorDAO $sponsorDAO
    ) {
        $personExistsClosure = fn($value) => $this->personDAO->getPersonById($value) !== null;

        parent::__construct($contactDAO, $redirect, ContactType::ADD_SPONSOR, [
            new Field('senderFirstName', 'Votre prénom doit contenir au moins 1 caractère'),
            new Field('senderLastName', 'Votre nom doit contenir au moins 1 caractère'),
            new EmailField('senderEmail', 'Votre email doit être valide'),
            new NumberField('godFatherId', 'Le parrain doit être valide'),
            new CustomField('godFatherId', 'Le parrain doit exister', $personExistsClosure),
            new NumberField('godChildId', 'Le fillot doit être valide'),
            new CustomField('godChildId', 'Le fillot doit exister', $personExistsClosure),
            new NumberField('sponsorType', 'Le type de lien doit être valide'),
            new BoundedNumberField('sponsorType', 'Le type de lien doit être valide', 0, 1),
            new DateField('sponsorDate', 'La date doit être valide'),
        ]);
        $this->personDAO = $personDAO;
        $this->sponsorDAO = $sponsorDAO;
    }


    /**
     * Executes actions for adding a person. I basically store the request through the DAO.
     * @param array $data Data to execute
     * @return string error message if an error occurred, null otherwise
     */
    public function executeSuccess(array $data): string
    {

        if ($this->sponsorDAO->getSponsorByPeopleId($data['godFatherId'], $data['godChildId']) !== null) {
            return 'Le lien ne doit pas déjà exister';
        }

        $godFather = $this->personDAO->getPersonById($data['godFatherId']);
        $godChild = $this->personDAO->getPersonById($data['godChildId']);

        $sponsor = SponsorFactory::createSponsor(
            $data['sponsorType'],
            -1,
            $godFather,
            $godChild,
            $data['sponsorDate'],
            ''
        );

        $contact = new SponsorContact(
            -1,
            date('Y-m-d'),
            null,
            $data['senderFirstName'] . ' ' . $data['senderLastName'],
            $data['senderEmail'],
            ContactType::ADD_SPONSOR,
            $data['bonusInformation'] ?? '',
            $sponsor
        );

        $this->contactDAO->saveSponsorContact($contact);
        return '';
    }
}
