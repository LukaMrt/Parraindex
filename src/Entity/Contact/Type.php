<?php

declare(strict_types=1);

namespace App\Entity\Contact;

// WARNING : Don't change the id of the values because it's related to the contact form

/**
 * Type of contact
 */
enum Type: int
{
    case ADD_PERSON       = 0;
    case UPDATE_PERSON    = 1;
    case REMOVE_PERSON    = 2;
    case ADD_SPONSOR      = 3;
    case UPDATE_SPONSOR   = 4;
    case REMOVE_SPONSOR   = 5;
    case BUG              = 6;
    case CHOCKING_CONTENT = 7;
    case OTHER            = 8;
    case PASSWORD         = 9;

    /**
     * @return array<int, string>
     */
    public static function allTitles(): array
    {
        return [
            self::ADD_PERSON->value       => self::ADD_PERSON->toString(),
            self::UPDATE_PERSON->value    => self::UPDATE_PERSON->toString(),
            self::REMOVE_PERSON->value    => self::REMOVE_PERSON->toString(),
            self::ADD_SPONSOR->value      => self::ADD_SPONSOR->toString(),
            self::UPDATE_SPONSOR->value   => self::UPDATE_SPONSOR->toString(),
            self::REMOVE_SPONSOR->value   => self::REMOVE_SPONSOR->toString(),
            self::BUG->value              => self::BUG->toString(),
            self::CHOCKING_CONTENT->value => self::CHOCKING_CONTENT->toString(),
            self::OTHER->value            => self::OTHER->toString(),
            self::PASSWORD->value         => self::PASSWORD->toString(),
        ];
    }

    public function toString(): string
    {
        return match ($this) {
            self::ADD_PERSON       => "Ajout d'une personne",
            self::REMOVE_PERSON    => "Suppression d'une personne",
            self::UPDATE_PERSON    => "Modification d'une personne",
            self::ADD_SPONSOR      => "Ajout d'un lien",
            self::REMOVE_SPONSOR   => "Suppression d'un lien",
            self::UPDATE_SPONSOR   => "Modification d'un lien",
            self::BUG              => "Bug",
            self::CHOCKING_CONTENT => "Contenu choquant",
            self::OTHER            => "Autre",
            self::PASSWORD         => "Création d'un compte",
        };
    }
}
