<?php

namespace App\application\login;

use App\model\account\Account;
use App\model\account\Password;
use App\model\person\Identity;

interface AccountDAO
{
    public function getAccountPassword(string $login): Password;


    public function createAccount(Account $account): void;


    public function existsAccount(string $email): bool;


    public function existsAccountByIdentity(Identity $identity): bool;


    public function getSimpleAccount(mixed $username): Account;


    public function createTemporaryAccount(Account $account, string $link): void;


    public function getTemporaryAccountByToken(string $token): Account;


    public function deleteTemporaryAccount(Account $account): void;


    public function getAccountByLogin(string $email): ?Account;


    public function createResetpassword(Account $account, string $token): void;


    public function getAccountResetPasswordByToken(string $token): Account;


    public function editAccountPassword(Account $account): void;


    public function deleteResetPassword(Account $account): void;
}
