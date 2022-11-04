<?php

namespace App\controller;

use App\infrastructure\database\DatabaseConnection;
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
			$conn = new DatabaseConnection();
			$database = $conn->getDatabase();
			$email = $_POST['email'];
			$password = $_POST['password'];
			$passwordConfirm = $_POST['password-confirm'];
			$name = $_POST['name'];
			$firstname = $_POST['firstname'];
			if ($password == $passwordConfirm) {
				$this->createAccount($router,$database,$email,$password,$name,$firstname);
			} else {
				$error = 'Les mots de passe ne correspondent pas';
			}
		} else {
			$error = 'Veuillez remplir tous les champs';
		}
		$this->render('signup.twig', ['router' => $router, 'error' => $error ?? '']);
	}

	private function createAccount($router,$database,$email,$password,$name,$firstname) : void {
		$sql = $database->prepare(
			"INSERT INTO Account (email, password, id_person) 
			VALUES (:email, :password, (SELECT P.id_person FROM Person P WHERE last_name = :name AND first_name = :firstname))");
		$sql->bindParam(':email', $email);
		$password = password_hash($password, PASSWORD_DEFAULT);
		$sql->bindParam(':password', $password);
		$sql->bindParam(':name', $name);
		$sql->bindParam(':firstname', $firstname);
		$sql->execute();
		header('Location: ' . $router->url('home'));
	}
}