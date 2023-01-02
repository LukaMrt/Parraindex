<?php

namespace App\application\contact\field;

class Field
{
    private string $name;
    private string $error;

    public function __construct(string $name, string $error)
    {
        $this->name = $name;
        $this->error = $error;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function isValid(string $value): bool
    {
        return !empty(trim($value));
    }
}
