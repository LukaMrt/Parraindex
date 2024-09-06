<?php

namespace App\Entity\account;

use App\Entity\school\School;

/**
 * Privilege of an account
 */
class Privilege
{
    /**
     * @var School school related to the privilege
     */
    private School $school;
    /**
     * @var PrivilegeType value of the privilege
     */
    private PrivilegeType $type;


    /**
     * @param School $school school related to the privilege
     * @param PrivilegeType $type value of the privilege
     */
    public function __construct(School $school, PrivilegeType $type)
    {
        $this->school = $school;
        $this->type = $type;
    }


    /**
     * Verify if the privilege is higher than the given privilege
     * @param PrivilegeType $other privilege to compare
     * @return bool true if the privilege is higher than the given privilege
     */
    public function isHigherThan(PrivilegeType $other): bool
    {
        return $this->type->isHigherThan($other);
    }


    /**
     * @return PrivilegeType value of the privilege
     */
    public function getPrivilegeType(): PrivilegeType
    {
        return $this->type;
    }
}
