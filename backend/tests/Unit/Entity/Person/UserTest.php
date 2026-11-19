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
        self::assertContains('ROLE_USER', $result);
        self::assertContains('ROLE_ADMIN', $result);
    }

    public function testGetRolesDoesNotDuplicateUserRole(): void
    {
        // Given
        $user = new User();

        // When
        $result = $user->getRoles();

        // Then
        self::assertCount(1, $result);
        self::assertSame(['ROLE_USER'], $result);
    }

    public function testIsAdminReturnsTrueWhenUserHasAdminRole(): void
    {
        // Given
        $user = new User();
        $user->setRoles([Role::ADMIN, Role::USER]);

        // When
        $result = $user->isAdmin();

        // Then
        self::assertTrue($result);
    }

    public function testIsAdminReturnsFalseWhenUserHasNoAdminRole(): void
    {
        // Given
        $user = new User();
        $user->setRoles([Role::USER]);

        // When
        $result = $user->isAdmin();

        // Then
        self::assertFalse($result);
    }

    public function testGetPictureReturnsNullWhenNotSet(): void
    {
        // Given
        $user = new User();

        // When
        $result = $user->getPicture();

        // Then
        self::assertNull($result);
    }

    public function testGetPictureReturnsActualPictureWhenSet(): void
    {
        // Given
        $user = new User();
        $user->setPicture('custom.jpg');

        // When
        $result = $user->getPicture();

        // Then
        self::assertSame('custom.jpg', $result);
    }

    public function testGetUserIdentifierThrowsExceptionWhenEmailIsNull(): void
    {
        // Given
        $user = new User();

        // Then
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageIsOrContains('The email of the user is not set.');

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
        self::assertSame('john.doe@etu.univ-lyon1.fr', $result);
    }

    public function testGetRolesEnumConvertsStringsToEnums(): void
    {
        // Given
        $user = new User();
        $user->setRoles([Role::ADMIN, Role::USER]);

        // When
        $result = $user->getRolesEnum();

        // Then
        self::assertCount(2, $result);
        self::assertContainsOnlyInstancesOf(Role::class, $result);
        self::assertContains(Role::ADMIN, $result);
        self::assertContains(Role::USER, $result);
    }

    public function testSetRolesConvertsEnumsToStrings(): void
    {
        // Given
        $user = new User();

        // When
        $user->setRoles([Role::ADMIN, Role::USER]);

        $result = $user->getRoles();

        // Then
        self::assertContains('ROLE_ADMIN', $result);
        self::assertContains('ROLE_USER', $result);
    }
}
