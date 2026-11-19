<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Person;

use App\Entity\Characteristic\Characteristic;
use App\Entity\Characteristic\CharacteristicType;
use App\Entity\Person\Person;
use App\Entity\Sponsor\Sponsor;
use PHPUnit\Framework\TestCase;

final class PersonTest extends TestCase
{
    public function testSetFirstNameCapitalizesFirstLetter(): void
    {
        // Given
        $person = new Person();

        // When
        $person->setFirstName('john');

        // Then
        self::assertSame('John', $person->getFirstName());
    }

    public function testSetFirstNameLowercasesRest(): void
    {
        // Given
        $person = new Person();

        // When
        $person->setFirstName('JOHN');

        // Then
        self::assertSame('John', $person->getFirstName());
    }

    public function testSetLastNameCapitalizesFirstLetter(): void
    {
        // Given
        $person = new Person();

        // When
        $person->setLastName('doe');

        // Then
        self::assertSame('Doe', $person->getLastName());
    }

    public function testSetLastNameLowercasesRest(): void
    {
        // Given
        $person = new Person();

        // When
        $person->setLastName('DOE');

        // Then
        self::assertSame('Doe', $person->getLastName());
    }

    public function testGetFullNameReturnsFormattedName(): void
    {
        // Given
        $person = new Person();
        $person->setFirstName('John');
        $person->setLastName('Doe');

        // When
        $result = $person->getFullName();

        // Then
        self::assertSame('John DOE', $result);
    }

    public function testConstructorInitializesCollections(): void
    {
        // Given & When
        $person = new Person();

        // Then
        self::assertCount(0, $person->getGodFathers());
        self::assertCount(0, $person->getGodChildren());
        self::assertCount(0, $person->getCharacteristics());
    }

    public function testEqualsReturnsTrueForSameId(): void
    {
        // Given
        $person1 = new Person();
        $person1->setId(1);

        $person2 = new Person();
        $person2->setId(1);

        // When
        $result = $person1->equals($person2);

        // Then
        self::assertTrue($result);
    }

    public function testEqualsReturnsFalseForDifferentId(): void
    {
        // Given
        $person1 = new Person();
        $person1->setId(1);

        $person2 = new Person();
        $person2->setId(2);

        // When
        $result = $person1->equals($person2);

        // Then
        self::assertFalse($result);
    }

    public function testEqualsReturnsFalseForNull(): void
    {
        // Given
        $person = new Person();
        $person->setId(1);

        // When
        $result = $person->equals(null);

        // Then
        self::assertFalse($result);
    }

    public function testCreateMissingCharacteristicsAddsNewCharacteristics(): void
    {
        // Given
        $person = new Person();

        $type1 = new CharacteristicType();
        $type1->setId(1);

        $type2 = new CharacteristicType();
        $type2->setId(2);

        // When
        $person->createMissingCharacteristics([$type1, $type2]);

        // Then
        self::assertCount(2, $person->getCharacteristics());
    }

    public function testCreateMissingCharacteristicsDoesNotAddExistingCharacteristics(): void
    {
        // Given
        $person = new Person();

        $type1 = new CharacteristicType();
        $type1->setId(1);

        $characteristic = new Characteristic();
        $characteristic->setType($type1);
        $characteristic->setVisible(false);

        $person->addCharacteristic($characteristic);

        // When
        $person->createMissingCharacteristics([$type1]);

        // Then
        self::assertCount(1, $person->getCharacteristics());
    }

    public function testAddGodFatherAddsToCollection(): void
    {
        // Given
        $person  = new Person();
        $sponsor = new Sponsor();

        // When
        $person->addGodFather($sponsor);

        // Then
        self::assertCount(1, $person->getGodFathers());
        self::assertTrue($person->getGodFathers()->contains($sponsor));
    }

    public function testRemoveGodFatherRemovesFromCollection(): void
    {
        // Given
        $person  = new Person();
        $sponsor = new Sponsor();
        $person->addGodFather($sponsor);

        // When
        $person->removeGodFather($sponsor);

        // Then
        self::assertCount(0, $person->getGodFathers());
    }

    public function testAddGodChildAddsToCollection(): void
    {
        // Given
        $person  = new Person();
        $sponsor = new Sponsor();

        // When
        $person->addGodChild($sponsor);

        // Then
        self::assertCount(1, $person->getGodChildren());
        self::assertTrue($person->getGodChildren()->contains($sponsor));
    }

    public function testRemoveGodChildRemovesFromCollection(): void
    {
        // Given
        $person  = new Person();
        $sponsor = new Sponsor();
        $person->addGodChild($sponsor);

        // When
        $person->removeGodChild($sponsor);

        // Then
        self::assertCount(0, $person->getGodChildren());
    }
}
