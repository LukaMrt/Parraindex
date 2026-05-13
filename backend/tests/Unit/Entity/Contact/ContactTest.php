<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Contact;

use App\Entity\Contact\Contact;
use App\Entity\Sponsor\Type as SponsorType;
use PHPUnit\Framework\TestCase;

final class ContactTest extends TestCase
{
    public function testGetEntryYearReturns2022ByDefault(): void
    {
        // Given
        $contact = new Contact();

        // When
        $result = $contact->getEntryYear();

        // Then
        $this->assertSame(2022, $result);
    }

    public function testGetEntryYearReturnsActualValueWhenSet(): void
    {
        // Given
        $contact = new Contact();
        $contact->setEntryYear(2024);

        // When
        $result = $contact->getEntryYear();

        // Then
        $this->assertSame(2024, $result);
    }

    public function testGetRelatedPersonFirstNameReturnsEmptyStringByDefault(): void
    {
        // Given
        $contact = new Contact();

        // When
        $result = $contact->getRelatedPersonFirstName();

        // Then
        $this->assertSame('', $result);
    }

    public function testGetRelatedPersonLastNameReturnsEmptyStringByDefault(): void
    {
        // Given
        $contact = new Contact();

        // When
        $result = $contact->getRelatedPersonLastName();

        // Then
        $this->assertSame('', $result);
    }

    public function testGetRelatedPerson2FirstNameReturnsEmptyStringByDefault(): void
    {
        // Given
        $contact = new Contact();

        // When
        $result = $contact->getRelatedPerson2FirstName();

        // Then
        $this->assertSame('', $result);
    }

    public function testGetRelatedPerson2LastNameReturnsEmptyStringByDefault(): void
    {
        // Given
        $contact = new Contact();

        // When
        $result = $contact->getRelatedPerson2LastName();

        // Then
        $this->assertSame('', $result);
    }

    public function testGetSponsorTypeReturnsUnknownByDefault(): void
    {
        // Given
        $contact = new Contact();

        // When
        $result = $contact->getSponsorType();

        // Then
        $this->assertSame(SponsorType::UNKNOWN, $result);
    }
}
