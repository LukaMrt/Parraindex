<?php

namespace App\application\contact\field;

use DateTime;

/**
 * This class is the date version of the Field class. It verifies if the value is a valid date.
 */
class DateField extends Field
{

    /**
     * Verify if value is a valid date. A date is valid if it is a date in the format YYYY-MM-DD and
     * if it is after 2010.
     * @param string $value The value to verify
     * @return bool True if value is a valid date, false otherwise
     */
    public function isValid(string $value): bool
    {
        $date = DateTime::createFromFormat('Y-m-d', $value);
        return $date
            && $date->format('Y-m-d') === $value
            && DateTime::createFromFormat('Y-m-d', '2010-01-01') <= $date;
    }

}
