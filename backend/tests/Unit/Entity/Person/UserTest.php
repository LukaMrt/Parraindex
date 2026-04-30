<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Person;

use App\Entity\Person\Role;
use App\Entity\Person\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testGetRolesAutomaticallyAddsUserRole(): void
    {
        // Given
        $user = new User();
        $user->setRoles([Role::ADMIN]);

        // When
        $result = $user->getRoles();

        // Then
        $this->assertContains('ROLE_USER', $result);
        $this->assertContains('ROLE_ADMIN', $result);
    }

    public function testGetRolesDoesNotDuplicateUserRole(): void
    {
        // Given
        $user = new User();

        // When
        $result = $user->getRoles();

        // Then
        $this->assertCount(1, $result);
        $this->assertSame(['ROLE_USER'], $result);
    }

    public function testIsAdminReturnsTrueWhenUserHasAdminRole(): void
    {
        // Given
        $user = new User();
        $user->setRoles([Role::ADMIN, Role::USER]);

        // When
        $result = $user->isAdmin();

        // Then
        $this->assertTrue($result);
    }

    public function testIsAdminReturnsFalseWhenUserHasNoAdminRole(): void
    {
        // Given
        $user = new User();
        $user->setRoles([Role::USER]);

        // When
        $result = $user->isAdmin();

        // Then
        $this->assertFalse($result);
    }

    public function testGetPictureReturnsDefaultPictureWhenNull(): void
    {
        // Given
        $user = new User();

        // When
        $result = $user->getPicture();

        // Then
        $this->assertSame('no-picture.svg', $result);
    }

    public function testGetPictureReturnsActualPictureWhenSet(): void
    {
        // Given
        $user = new User();
        $user->setPicture('custom.jpg');

        // When
        $result = $user->getPicture();

        // Then
        $this->assertSame('custom.jpg', $result);
    }

    public function testGetUserIdentifierThrowsExceptionWhenEmailIsNull(): void
    {
        // Given
        $user = new User();

        // Then
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The email of the user is not set.');

        // When
        $user->getUserIdentifier();
    }

    public function testGetUserIdentifierReturnsEmailWhenSet(): void
    {
        // Given
        $user = new User();
        $user->setEmail('john.doe@etu.univ-lyon1.fr');

        // When
        $result = $user->getUserIdentifier();

        // Then
        $this->assertSame('john.doe@etu.univ-lyon1.fr', $result);
    }

    public function testGetRolesEnumConvertsStringsToEnums(): void
    {
        // Given
        $user = new User();
        $user->setRoles([Role::ADMIN, Role::USER]);

        // When
        $result = $user->getRolesEnum();

        // Then
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(Role::class, $result);
        $this->assertContains(Role::ADMIN, $result);
        $this->assertContains(Role::USER, $result);
    }

    public function testSetRolesConvertsEnumsToStrings(): void
    {
        // Given
        $user = new User();

        // When
        $user->setRoles([Role::ADMIN, Role::USER]);

        $result = $user->getRoles();

        // Then
        $this->assertContains('ROLE_ADMIN', $result);
        $this->assertContains('ROLE_USER', $result);
    }
}
