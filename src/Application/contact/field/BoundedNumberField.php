<?php

declare(strict_types=1);

namespace App\Application\contact\field;

/**
 * This class is the bounded number of the Field class. It verifies that the value is
 * a number and that it is between the minimum and maximum values.
 */
class BoundedNumberField extends Field
{
    /**
     * @var int The minimum value
     */
    private int $min;

    /**
     * @var int The maximum value
     */
    private int $max;


    /**
     * @param string $name The name of the field
     * @param string $error The error message to display
     * @param int $min The minimum value
     * @param int $max The maximum value
     */
    public function __construct(string $name, string $error, int $min, int $max)
    {
        parent::__construct($name, $error);
        $this->min = min($min, $max);
        $this->max = max($min, $max);
    }


    /**
     * Verifies that the value is a number and that it is between the minimum and maximum values.
     * @param $value string The value to validate
     * @return bool True if the value is valid, false otherwise
     */
    #[\Override]
    public function isValid(string $value): bool
    {
        return is_numeric($value) && $this->min <= $value && $value <= $this->max;
    }
}
