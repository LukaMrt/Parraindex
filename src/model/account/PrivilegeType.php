<?php

namespace App\model\account;

enum PrivilegeType
{

    case ADMIN;
    case TEACHER;
    case STUDENT;


    public static function fromString(string $type): PrivilegeType
    {
        return match ($type) {
            'ADMIN' => self::ADMIN,
            'TEACHER' => self::TEACHER,
            'STUDENT' => self::STUDENT,
        };
    }


    public function isHigherThan(PrivilegeType $highest): bool
    {
        return $this->ordinal() < $highest->ordinal();
    }


    private function ordinal(): int
    {
        return match ($this) {
            self::ADMIN => 0,
            self::TEACHER => 1,
            self::STUDENT => 2,
        };
    }


    public function toString(): string
    {
        return match ($this) {
            self::ADMIN => 'ADMIN',
            self::TEACHER => 'TEACHER',
            self::STUDENT => 'STUDENT',
        };
    }

}
