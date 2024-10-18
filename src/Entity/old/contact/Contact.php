<?php

declare(strict_types=1);

namespace App\Entity\old\contact;

use App\Entity\Contact\Type;

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
     * @var Type The type of the contact
     */
    private Type $type;

    /**
     * @var string The description of the contact message
     */
    private string $description;

    /**
     * @var string The date where the contact message was sent
     */
    private string $contactDate;

    /**
     * @var ?string The date where the contact message was resolved
     */
    private ?string $contactResolution;


    /**
     * @param int $id The id of the contact
     * @param string $contactDate The date where the contact message was sent
     * @param ?string $contactResolution The date where the contact message was resolved
     * @param string $contacterName The name of the person who sent the contact
     * @param string $contacterEmail The email of the person who sent the contact
     * @param Type $type The type of the contact
     * @param string $description The description of the contact message
     */
    public function __construct(
        int $id,
        string $contactDate,
        ?string $contactResolution,
        string $contacterName,
        string $contacterEmail,
        Type $type,
        string $description
    ) {
        $this->id                = $id;
        $this->contactDate       = $contactDate;
        $this->contactResolution = $contactResolution;
        $this->contacterName     = ucwords(strtolower($contacterName));
        $this->contacterEmail    = $contacterEmail;
        $this->type              = $type;
        $this->description       = $description;
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
     * @return string The date where the contact message was resolved
     */
    public function getContactDate(): string
    {
        return $this->contactDate;
    }


    /**
     * @return ?string The date where the contact message was resolved
     */
    public function getContactResolution(): ?string
    {
        return $this->contactResolution;
    }


    /**
     * @return array[] The title description of the contact elements
     */
    abstract public function getDescription(): array;
}
