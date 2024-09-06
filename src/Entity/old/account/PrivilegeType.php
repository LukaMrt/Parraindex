<?php

namespace App\Entity\account;

/**
 * Privilege values
 */
enum PrivilegeType
{
    case ADMIN;
    case TEACHER;
    case STUDENT;


    /**
     * Get the privilege value from the string
     * @param string $type value to convert
     * @return PrivilegeType converted value
     */
    public static function fromString(string $type): PrivilegeType
    {
        return match ($type) {
            'ADMIN' => self::ADMIN,
            'TEACHER' => self::TEACHER,
            'STUDENT' => self::STUDENT,
        };
    }


    /**
     * Verify if the privilege is higher than the other
     * @param PrivilegeType $other privilege to compare
     * @return bool true if the privilege is higher
     */
    public function isHigherThan(PrivilegeType $other): bool
    {
        return $this->ordinal() < $other->ordinal();
    }


    /**
     * @return int the ordinal value of the privilege
     */
    private function ordinal(): int
    {
        return match ($this) {
            self::ADMIN => 0,
            self::TEACHER => 1,
            self::STUDENT => 2,
        };
    }


    /**
     * @return string the string value of the privilege
     */
    public function toString(): string
    {
        return match ($this) {
            self::ADMIN => 'ADMIN',
            self::TEACHER => 'TEACHER',
            self::STUDENT => 'STUDENT',
        };
    }
}
