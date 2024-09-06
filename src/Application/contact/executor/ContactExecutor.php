<?php

namespace App\Application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\field\Field;
use App\Application\redirect\Redirect;
use App\Entity\contact\ContactType;

/**
 * Represents an executor of a contact which perform contact form validation
 */
abstract class ContactExecutor
{
    /**
     * @var ContactDAO $contactDAO DAO to save the contact
     */
    protected ContactDAO $contactDAO;
    /**
     * @var ContactType $contactType Type of the contact
     */
    protected ContactType $contactType;
    /**
     * @var Redirect $redirect The redirect service to redirect the user
     */
    private Redirect $redirect;
    /**
     * @var array $fields Fields of the contact form
     */
    private array $fields;


    /**
     * @param ContactDAO $contactDAO DAO to save the contact
     * @param Redirect $redirect The redirect service to redirect the user
     * @param ContactType $contactType Type of the contact
     * @param array $fields Fields of the contact form
     */
    public function __construct(ContactDAO $contactDAO, Redirect $redirect, ContactType $contactType, array $fields)
    {
        $this->contactDAO = $contactDAO;
        $this->redirect = $redirect;
        $this->contactType = $contactType;
        $this->fields = $fields;
    }


    /**
     * @return int The number of fields
     */
    public function getId(): int
    {
        return $this->contactType->value;
    }


    /**
     * Performs the analysis of the contact form
     * @param array $data The data of the contact form
     * @return string The error message if the contact form is invalid, an empty string otherwise
     */
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


    /**
     * Verifies if the contact form fields are valid
     * @param array $data The data of the contact form
     * @return string The error message if the contact form is invalid, an empty string otherwise
     */
    protected function validate(array $data): string
    {

        $errors = [];

        foreach ($this->fields as $field) {
            $errors[] = $this->validateField($field, $data);
        }

        $errors = array_filter($errors, fn($error) => !empty($error));

        return implode('<br>', $errors);
    }


    /**
     * Verifies if a field of the contact form is valid
     * @param Field $field The field validator
     * @param array $data The data of the contact form
     * @return string The error message if the field is invalid, an empty string otherwise
     */
    private function validateField(Field $field, array $data): string
    {

        $error = '';

        if (!isset($data[$field->getName()]) || !$field->isValid($data[$field->getName()])) {
            $error = $field->getError();
        }

        return $error;
    }


    /**
     * Performs the actions to execute if the contact form is valid
     * @param array $data The data of the contact form
     * @return string The error message if the contact form is invalid, an empty string otherwise
     */
    abstract public function executeSuccess(array $data): string;
}
