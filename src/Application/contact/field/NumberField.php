<?php

declare(strict_types=1);

namespace App\Application\contact\field;

/**
 * This class is the number version of the field class. It verifies that the value is a number
 */
class NumberField extends Field
{
    /**
     * Verify that the value is a number
     * @param string $value The value to verify
     * @return bool True if the value is a number, false otherwise
     */
    #[\Override]
    public function isValid(string $value): bool
    {
        return is_numeric($value);
    }
}
