<?php

namespace App\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\person\PersonDAO;
use App\application\redirect\Redirect;
use App\application\sponsor\SponsorDAO;
use App\model\contact\ContactType;

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
