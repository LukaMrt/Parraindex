<?php

namespace unit\model\account;

use App\model\account\PrivilegeType;
use PHPUnit\Framework\TestCase;

class PrivilegeTypeTest extends TestCase {

	public function testFromstringReturnsCorrectPrivilegeType() {
		$this->assertEquals(PrivilegeType::ADMIN, PrivilegeType::fromString('ADMIN'));
	}

}