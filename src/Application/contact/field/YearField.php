<?php

declare(strict_types=1);

namespace App\Application\contact\field;

/**
 * This class is the year format version of the Field class. It verifies if the value is a valid year.
 */
class YearField extends Field
{
    /**
     * Verify if the value is a valid year. A year is valid if it is a number between 2010 and now.
     * @param string $value The value to verify.
     * @return bool True if the value is a valid year, false otherwise.
     */
    #[\Override]
    public function isValid(string $value): bool
    {
        return is_numeric($value) && 2010 <= $value && $value <= date('Y');
    }
}
