<?php

namespace App\model\account;

use App\model\school\School;

class Privilege {

    private School $school;
    private PrivilegeType $type;

    public function __construct(School $school, PrivilegeType $type) {
        $this->school = $school;
        $this->type = $type;
    }

    public function isHigherThan(PrivilegeType $highest): bool {
        return $this->type->isHigherThan($highest);
    }

    public function getPrivilegeType(): PrivilegeType {
        return $this->type;
    }

}