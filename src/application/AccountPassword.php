<?php

namespace App\application;

class AccountPassword{
	private string $password;

	public function __construct(string $password){
		$this->password = $password;
	}

	public function getPassword(): string{
		return $this->password;
	}

	public function hashPassword(): void{
		$this->password = password_hash($this->password, PASSWORD_DEFAULT);
	}

	public function checkPassword($passwordConfirm): bool {
		return password_verify($this->getPassword(),$passwordConfirm);
	}
}