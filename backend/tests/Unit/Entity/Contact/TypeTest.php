<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Contact;

use App\Entity\Contact\Type;
use PHPUnit\Framework\TestCase;

final class TypeTest extends TestCase
{
    public function testToStringReturnsCorrectDescriptionForAddPerson(): void
    {
        // Given
        $type = Type::ADD_PERSON;

        // When
        $result = $type->toString();

        // Then
        $this->assertSame("Ajout d'une personne", $result);
    }

    public function testToStringReturnsCorrectDescriptionForUpdatePerson(): void
    {
        // Given
        $type = Type::UPDATE_PERSON;

        // When
        $result = $type->toString();

        // Then
        $this->assertSame("Modification d'une personne", $result);
    }

    public function testToStringReturnsCorrectDescriptionForRemovePerson(): void
    {
        // Given
        $type = Type::REMOVE_PERSON;

        // When
        $result = $type->toString();

        // Then
        $this->assertSame("Suppression d'une personne", $result);
    }

    public function testToStringReturnsCorrectDescriptionForAddSponsor(): void
    {
        // Given
        $type = Type::ADD_SPONSOR;

        // When
        $result = $type->toString();

        // Then
        $this->assertSame("Ajout d'un lien", $result);
    }

    public function testToStringReturnsCorrectDescriptionForUpdateSponsor(): void
    {
        // Given
        $type = Type::UPDATE_SPONSOR;

        // When
        $result = $type->toString();

        // Then
        $this->assertSame("Modification d'un lien", $result);
    }

    public function testToStringReturnsCorrectDescriptionForRemoveSponsor(): void
    {
        // Given
        $type = Type::REMOVE_SPONSOR;

        // When
        $result = $type->toString();

        // Then
        $this->assertSame("Suppression d'un lien", $result);
    }

    public function testToStringReturnsCorrectDescriptionForBug(): void
    {
        // Given
        $type = Type::BUG;

        // When
        $result = $type->toString();

        // Then
        $this->assertSame('Bug', $result);
    }

    public function testToStringReturnsCorrectDescriptionForChockingContent(): void
    {
        // Given
        $type = Type::CHOCKING_CONTENT;

        // When
        $result = $type->toString();

        // Then
        $this->assertSame('Contenu choquant', $result);
    }

    public function testToStringReturnsCorrectDescriptionForOther(): void
    {
        // Given
        $type = Type::OTHER;

        // When
        $result = $type->toString();

        // Then
        $this->assertSame('Autre', $result);
    }

    public function testToStringReturnsCorrectDescriptionForPassword(): void
    {
        // Given
        $type = Type::PASSWORD;

        // When
        $result = $type->toString();

        // Then
        $this->assertSame("Création d'un compte", $result);
    }

    public function testAllTitlesReturnsAllTenTypes(): void
    {
        // Given & When
        $result = Type::allTitles();

        // Then
        $this->assertCount(10, $result);
        $this->assertArrayHasKey(0, $result); // ADD_PERSON
        $this->assertArrayHasKey(1, $result); // UPDATE_PERSON
        $this->assertArrayHasKey(2, $result); // REMOVE_PERSON
        $this->assertArrayHasKey(3, $result); // ADD_SPONSOR
        $this->assertArrayHasKey(4, $result); // UPDATE_SPONSOR
        $this->assertArrayHasKey(5, $result); // REMOVE_SPONSOR
        $this->assertArrayHasKey(6, $result); // BUG
        $this->assertArrayHasKey(7, $result); // CHOCKING_CONTENT
        $this->assertArrayHasKey(8, $result); // OTHER
        $this->assertArrayHasKey(9, $result); // PASSWORD
    }
}
