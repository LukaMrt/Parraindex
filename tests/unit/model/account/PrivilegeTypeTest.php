<?php

namespace unit\model\account;

use App\model\account\PrivilegeType;
use PHPUnit\Framework\TestCase;

class PrivilegeTypeTest extends TestCase {

	public function testFromstringReturnsCorrectPrivilegeType() {
		$this->assertEquals(PrivilegeType::ADMIN, PrivilegeType::fromString('ADMIN'));
		$this->assertEquals(PrivilegeType::TEACHER, PrivilegeType::fromString('TEACHER'));
	}

	public function testTostringReturnsCorrectValue() {
		$this->assertEquals('TEACHER', PrivilegeType::TEACHER->toString());
	}

}