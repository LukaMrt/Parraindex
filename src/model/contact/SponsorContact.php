<?php

namespace App\model\contact;

use App\model\sponsor\Sponsor;

class SponsorContact extends Contact
{
    private Sponsor $sponsor;


    public function __construct(
        int $id,
        string $contacterName,
        string $contacterEmail,
        ContactType $type,
        string $description,
        Sponsor $sponsor
    ) {
        parent::__construct($id, $contacterName, $contacterEmail, $type, $description);
        $this->sponsor = $sponsor;
    }


    public function getDescription(): array
    {

        $godFather = $this->sponsor->getGodFather();
        $godChild = $this->sponsor->getGodChild();

        return [
            ['Parrain', $godFather->getFirstName() . ' ' . $godFather->getLastName()],
            ['Fillot', $godChild->getFirstName() . ' ' . $godChild->getLastName()],
            ['Type de parrainage', $this->sponsor->getType()],
            ['Date du parrainage', $this->sponsor->getDate()->format('d/m/Y')]
        ];
    }


    public function getSponsor(): Sponsor
    {
        return $this->sponsor;
    }
}
