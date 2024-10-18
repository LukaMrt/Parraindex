<?php

declare(strict_types=1);

namespace App\Application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\field\CustomField;
use App\Application\contact\field\EmailField;
use App\Application\contact\field\Field;
use App\Application\contact\field\NumberField;
use App\Application\person\PersonDAO;
use App\Application\redirect\Redirect;
use App\Application\sponsor\SponsorDAO;
use App\Entity\Contact\Type;
use App\Entity\old\contact\SponsorContact;

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
     * @param Type $contactType Contact type
     * @param SponsorDAO $sponsorDAO DAO for sponsors
     * @param PersonDAO $personDAO DAO for persons
     */
    public function __construct(
        ContactDAO $contactDAO,
        Redirect $redirect,
        Type $contactType,
        SponsorDAO $sponsorDAO,
        PersonDAO $personDAO
    ) {
        $personExistsClosure = fn($value): bool => $personDAO->getPersonById($value) instanceof \App\Entity\old\person\Person;
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
        $this->sponsorDAO    = $sponsorDAO;
    }


    /**
     * Performs the actions when the contact is valid
     * @param array $data Data from the form
     * @return string error message or empty string if no error
     */
    #[\Override]
    public function executeSuccess(array $data): string
    {

        $sponsor = $this->sponsorDAO->getSponsorByPeopleId($data['godFatherId'], $data['godChildId']);

        if (!$sponsor instanceof \App\Entity\old\sponsor\Sponsor) {
            return 'Le lien doit exister';
        }

        $sponsorContact = new SponsorContact(
            -1,
            date('Y-m-d'),
            null,
            $data['senderFirstName'] . ' ' . $data['senderLastName'],
            $data['senderEmail'],
            $this->contactType,
            $data['message'] ?? '',
            $sponsor
        );

        $this->contactDAO->saveSponsorContact($sponsorContact);
        return '';
    }
}
