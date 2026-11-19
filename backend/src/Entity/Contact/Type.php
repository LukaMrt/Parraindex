<?php

declare(strict_types=1);

namespace App\Entity\Contact;

// WARNING : Don't change the id of the values because it's related to the contact form

/**
 * Type of contact
 */
enum Type: int
{
    case ADD_PERSON     = 0;
    case UPDATE_PERSON  = 1;
    case REMOVE_PERSON  = 2;
    case ADD_SPONSOR    = 3;
    case UPDATE_SPONSOR = 4;
    case REMOVE_SPONSOR = 5;
    case BUG = 6;
    case CHOCKING_CONTENT = 7;
    case OTHER    = 8;
    case PASSWORD = 9;
}
