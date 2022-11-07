<?php

namespace App\application;

use App\infrastructure\accountService\MysqlAccountDAO;

class Login{
	public function __construct($router){
		$this->login($router);
	}

	public function login($router): ?string{
		if (isset($_POST['login'],$_POST['login-password'])) {
			$login = $_POST['login'];
			$password = new AccountPassword($_POST['login-password']);
			$accountService = new MysqlAccountDAO();
			$account = $accountService->getAccount($login);
			if($account!=null){
				$passwordConfirm = $account->password;
				if ($password->checkPassword($passwordConfirm)) {
					$_SESSION['login'] = $login;
					header('Location: ' . $router->url('home'));
					return null;
				}
				else {
					$error = 'Mot de passe incorrect';
				}
			}
			else {
				$error = 'Identifiant incorrect';
			}
		} else {
			$error = "Veuillez remplir tous les champs";
		}
		return $error;
	}
}