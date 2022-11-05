<?php

namespace App\controller;

use App\infrastructure\accountService\MysqlAccountDAO;
use App\infrastructure\router\Router;
use Twig\Environment;

class SignUpController extends Controller {
	public function __construct(Environment $twig) {
		parent::__construct($twig);
	}

	public function get(Router $router, array $parameters): void {
		$this->render('signup.twig', ['router' => $router]);
	}

	public function post(Router $router, array $parameters): void {
		if (isset($_POST['email'],$_POST['password'],$_POST['password-confirm'])) {
			$email = $_POST['email'];
			$password =$_POST['password'];
			$passwordConfirm =$_POST['password-confirm'];
			$name = $_POST['name'];
			$firstname = $_POST['firstname'];
			$accountService = new MysqlAccountDAO();
			if ($password == $passwordConfirm) {
				$accountService->createAccount($router,$email,$password,$name,$firstname);
			} else {
				$error = 'Les mots de passe ne correspondent pas';
			}
		} else {
			$error = 'Veuillez remplir tous les champs';
		}
		$this->render('signup.twig', ['router' => $router, 'error' => $error ?? '']);
	}


}