<?php

namespace App\controller;

use App\infrastructure\accountService\MysqlAccountDAO;
use App\application\AccountPassword;
use App\infrastructure\router\Router;
use Twig\Environment;

class LoginController extends Controller {

	public function __construct(Environment $twig) {
		parent::__construct($twig);
	}

	public function get(Router $router, array $parameters): void {
		$this->render('login.twig', ['router' => $router]);
	}

	public function post(Router $router, array $parameters): void {
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
		$this->render('login.twig', ['router' => $router, 'error' => $error ?? '']);
	}

}