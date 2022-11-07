<?php

namespace App\application\login;

use App\model\account\Password;

interface AccountDAO {

    public function getAccountPassword(string $login): Password;

    public function createAccount(string $email, string $password, string $name, string $firstname): void;

}