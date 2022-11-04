<?php

namespace App\model\account;

use App\model\school\School;

class Privilege {

	private School $school;
	private Account $account;
	private PrivilegeType $type;

	public function __construct(School $school, Account $account, PrivilegeType $type) {
		$this->school = $school;
		$this->account = $account;
		$this->type = $type;
	}

}