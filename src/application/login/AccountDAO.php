<?php

namespace App\application\login;

use App\model\account\Account;
use App\model\account\Password;
use App\model\person\Identity;

interface AccountDAO {

    public function getAccountPassword(string $login): Password;

    public function createAccount(Account $account): void;

	public function existsAccount(string $email): bool;

	public function existsAccountByIdentity(Identity $identity): bool;

    public function getSimpleAccount(mixed $username): Account;

}