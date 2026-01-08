<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Person;

use App\Entity\Person\Role;
use PHPUnit\Framework\TestCase;

final class RoleTest extends TestCase
{
    public function testToStringReturnsAdminForAdminRole(): void
    {
        // Given
        $role = Role::ADMIN;

        // When
        $result = $role->toString();

        // Then
        $this->assertSame('ADMIN', $result);
    }

    public function testToStringReturnsUserForUserRole(): void
    {
        // Given
        $role = Role::USER;

        // When
        $result = $role->toString();

        // Then
        $this->assertSame('USER', $result);
    }
}
