<?php

namespace App\application\contact\field;

/**
 * This class is used to verify if a field is valid or not in a form
 */
class Field
{
    /**
     * @var string the name of the field
     */
    private string $name;

    /**
     * @var string the error message to display if the field is not valid
     */
    private string $error;


    /**
     * Field constructor.
     * @param string $name the name of the field
     * @param string $error the error message to display if the field is not valid
     */
    public function __construct(string $name, string $error)
    {
        $this->name = $name;
        $this->error = $error;
    }


    /**
     * @return string the name of the field
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * @return string the error message to display if the field is not valid
     */
    public function getError(): string
    {
        return $this->error;
    }


    /**
     * @param string $value the value of the field
     * @return bool true if the field is valid, false otherwise
     */
    public function isValid(string $value): bool
    {
        return !empty(trim($value));
    }
}
