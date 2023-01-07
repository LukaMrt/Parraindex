<?php

namespace App\model\contact;

/**
 * Contact base class which represents a contact request
 */
abstract class Contact
{
    /**
     * @var int The id of the contact
     */
    private int $id;
    /**
     * @var string The name of the person who sent the contact
     */
    private string $contacterName;
    /**
     * @var string The email of the person who sent the contact
     */
    private string $contacterEmail;
    /**
     * @var ContactType The type of the contact
     */
    private ContactType $type;
    /**
     * @var string The description of the contact message
     */
    private string $description;


    /**
     * @param int $id The id of the contact
     * @param string $contacterName The name of the person who sent the contact
     * @param string $contacterEmail The email of the person who sent the contact
     * @param ContactType $type The type of the contact
     * @param string $description The description of the contact message
     */
    public function __construct(
        int $id,
        string $contacterName,
        string $contacterEmail,
        ContactType $type,
        string $description
    ) {
        $this->id = $id;
        $this->contacterName = $contacterName;
        $this->contacterEmail = $contacterEmail;
        $this->type = $type;
        $this->description = $description;
    }


    /**
     * @return int The id of the contact
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @return string The name of the person who sent the contact
     */
    public function getContacterName(): string
    {
        return $this->contacterName;
    }


    /**
     * @return string The email of the person who sent the contact
     */
    public function getContacterEmail(): string
    {
        return $this->contacterEmail;
    }


    /**
     * @return string The type of the contact
     */
    public function getType(): string
    {
        return $this->type->toString();
    }


    /**
     * @return string The description of the contact request
     */
    public function getMessage(): string
    {
        return $this->description;
    }


    /**
     * @return int The id of the contact type
     */
    public function getTypeId(): int
    {
        return $this->type->value;
    }


    /**
     * @return array[] The title description of the contact elements
     */
    abstract public function getDescription(): array;
}
