<?php

namespace App\Application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\person\PersonDAO;
use App\Application\redirect\Redirect;
use App\Application\sponsor\SponsorDAO;
use App\Entity\ContactType;

/**
 * Contact executor for the updating of a sponsor
 */
class UpdateSponsorContactExecutor extends SimpleSponsorContactExecutor
{
    /**
     * @param ContactDAO $contactDAO DAO for contacts
     * @param PersonDAO $personDAO DAO for persons
     * @param SponsorDAO $sponsorDAO DAO for sponsors
     * @param Redirect $redirect Redirect service
     */
    public function __construct(
        ContactDAO $contactDAO,
        PersonDAO $personDAO,
        SponsorDAO $sponsorDAO,
        Redirect $redirect
    ) {
        parent::__construct($contactDAO, $redirect, ContactType::UPDATE_SPONSOR, $sponsorDAO, $personDAO);
    }
}
