<?php

namespace App\Entity\contact;

use App\Entity\sponsor\Sponsor;

/**
 * Contact request related to a sponsor
 */
class SponsorContact extends Contact
{
    /**
     * @var Sponsor The sponsor related to the contact
     */
    private Sponsor $sponsor;


    /**
     * @param int $id The id of the contact
     * @param string $contactDate The date where the contact message was sent
     * @param ?string $contactResolution The date where the contact message was resolved
     * @param string $contacterName The name of the person who sent the contact
     * @param string $contacterEmail The email of the person who sent the contact
     * @param ContactType $type The type of the contact
     * @param string $description The description of the contact
     * @param Sponsor $sponsor The sponsor related to the contact
     */
    public function __construct(
        int $id,
        string $contactDate,
        ?string $contactResolution,
        string $contacterName,
        string $contacterEmail,
        ContactType $type,
        string $description,
        Sponsor $sponsor
    ) {
        parent::__construct(
            $id,
            $contactDate,
            $contactResolution,
            $contacterName,
            $contacterEmail,
            $type,
            $description
        );
        $this->sponsor = $sponsor;
    }


    /**
     * @return array[] The contact as an array
     */
    public function getDescription(): array
    {

        $godFather = $this->sponsor->getGodFather();
        $godChild = $this->sponsor->getGodChild();

        return [
            ['Parrain', $godFather->getFirstName() . ' SponsorContact.php' . $godFather->getLastName()],
            ['Fillot', $godChild->getFirstName() . ' SponsorContact.php' . $godChild->getLastName()],
            ['Type de parrainage', $this->sponsor->getType()],
            ['Date du parrainage', $this->sponsor->formatDate('d/m/Y')]
        ];
    }


    /**
     * @return Sponsor The sponsor related to the contact
     */
    public function getSponsor(): Sponsor
    {
        return $this->sponsor;
    }
}
