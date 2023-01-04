<?php

namespace App\application\contact\field;

use Closure;

/**
 * This class is the custom version of the Field class. It verifies that the value is valid by using a custom function.
 */
class CustomField extends Field
{

    /**
     * @var Closure The function that verifies the value
     */
    private Closure $validator;


    /**
     * @param string $name The name of the field
     * @param string $error The error message to display if the value is invalid
     * @param Closure $validator The function that verifies the value
     */
    public function __construct(string $name, string $error, Closure $validator)
    {
        parent::__construct($name, $error);
        $this->validator = $validator;
    }


    /**
     * Verifies that the value is valid by using the custom function.
     * @param string $value The value to verify
     * @return bool True if the value is valid, false otherwise
     */
    public function isValid(string $value): bool
    {
        return ($this->validator)($value);
    }

}
