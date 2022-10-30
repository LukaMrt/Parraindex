<?php

namespace App\model\association;

use App\model\user\User;
use DateTime;

class AssociationParticipation {

    private User $user;
    private Association $association;
    private Role $role;
    private DateTime $start;
    private ?DateTime $end;

    public function __construct(User $user, Association $association, Role $role, DateTime $start, ?DateTime $end) {
        $this->user = $user;
        $this->association = $association;
        $this->role = $role;
        $this->start = $start;
        $this->end = $end;
    }

    public function isOnGoing(): bool {
        return $this->end === null;
    }

}