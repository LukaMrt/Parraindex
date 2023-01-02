<?php

namespace App\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\field\Field;
use App\application\redirect\Redirect;
use App\model\contact\ContactType;

abstract class ContactExecutor
{
    protected ContactDAO $contactDAO;
    private Redirect $redirect;
    private ContactType $contactType;
    private array $fields;

    public function __construct(ContactDAO $contactDAO, Redirect $redirect, ContactType $contactType, array $fields)
    {
        $this->contactDAO = $contactDAO;
        $this->redirect = $redirect;
        $this->contactType = $contactType;
        $this->fields = $fields;
    }

    public function getId(): int
    {
        return $this->contactType->value;
    }

    public function execute(array $data): string
    {
        $errors = $this->validate($data);

        if (empty($errors)) {
            $errors = $this->executeSuccess($data);
        }

        if (empty($errors)) {
            $this->redirect->redirect('home');
        }

        return $errors;
    }

    protected function validate(array $data): string
    {

        $errors = [];

        foreach ($this->fields as $field) {
            $errors[] = $this->validateField($field, $data);
        }

        $errors = array_filter($errors, fn($error) => !empty($error));

        return implode('<br>', $errors);
    }

    private function validateField(Field $field, array $data): string
    {

        $error = '';

        if (!isset($data[$field->getName()]) || !$field->isValid($data[$field->getName()])) {
            $error = $field->getError();
        }

        return $error;
    }

    abstract public function executeSuccess(array $data): string;
}
