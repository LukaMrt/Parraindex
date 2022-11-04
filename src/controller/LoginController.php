<?php

namespace App\controller;

use App\infrastructure\database\DatabaseConnection;
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
			$password = $_POST['login-password'];
			$account = $this->getAccount($login);
			if($account!=null){
				if ($this->checkPassword($account,$password)) {
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

	public function getAccount($login){
		$conn = new DatabaseConnection();
		$database = $conn->getDatabase();
		$account = $database->prepare("SELECT * FROM Account WHERE email = :login");
		$account->bindParam(':login', $login);
		$account->execute();
		$result= $account->fetch();
		$conn = null;
		return $result;
	}

	private function checkPassword($account, string $password): bool {
		return password_verify($password,$account->password);
	}

}