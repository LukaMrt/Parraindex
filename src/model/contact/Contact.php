<?php

namespace App\model\contact;

abstract class Contact
{

    private int $id;
    private string $contacterName;
    private string $contacterEmail;
    private ContactType $type;
    private string $description;


    public function __construct(
        int         $id,
        string      $contacterName,
        string      $contacterEmail,
        ContactType $type,
        string      $description
    )
    {
        $this->id = $id;
        $this->contacterName = $contacterName;
        $this->contacterEmail = $contacterEmail;
        $this->type = $type;
        $this->description = $description;
    }


    public function getId(): int
    {
        return $this->id;
    }


    public function getContacterName(): string
    {
        return $this->contacterName;
    }


    public function getContacterEmail(): string
    {
        return $this->contacterEmail;
    }


    public function getType(): string
    {
        return $this->type->toString();
    }


    public function getMessage(): string
    {
        return $this->description;
    }


    public function getTypeId(): int
    {
        return $this->type->value;
    }


    abstract public function getDescription(): array;

}
