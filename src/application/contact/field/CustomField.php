<?php

namespace App\application\contact\field;

use Closure;

class CustomField extends Field
{
    private Closure $validator;


    public function __construct(string $name, string $error, Closure $validator)
    {
        parent::__construct($name, $error);
        $this->validator = $validator;
    }


    public function isValid(string $value): bool
    {
        return ($this->validator)($value);
    }
}
