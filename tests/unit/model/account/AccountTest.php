<?php

namespace unit\model\account;

use App\model\account\Account;
use App\model\account\Password;
use App\model\person\PersonBuilder;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase {

	private Account $account;

	public function setUp(): void {
		$this->account = new Account(1, 'test.test@etu.univ-lyon1.fr', PersonBuilder::aPerson()->build(), new Password('password'));
	}

	public function testGethashedpasswordHashReturnsDifferentPassword(): void {
		$this->assertNotEquals('password', $this->account->getHashedPassword());
	}

	public function testGethashedpasswordHashReturnsSamePasswordWhenPasswordIsAlreadyHashed(): void {
		$password = $this->account->getHashedPassword();
		$this->account = new Account(1, 'test.test@etu.univ-lyon1.fr', PersonBuilder::aPerson()->build(), new Password($password));
		$this->assertEquals($this->account->getHashedPassword(), $this->account->getHashedPassword());
	}

}
