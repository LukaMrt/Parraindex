<?php

namespace App\Application\contact\field;

/**
 * This class is the email version of the Field class. It verifies that the value is a valid email address.
 */
class EmailField extends Field
{
    /**
     * Verifies that the value is a valid email address using the PHP email filter.
     * @param string $value The value to verify.
     * @return bool True if the value is a valid email address, false otherwise.
     */
    public function isValid(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }
}
