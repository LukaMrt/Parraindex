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

		if (isset($_POST['email']) && isset($_POST['pwd']) && isset($_POST['pwd-confirm'])) {

			$conn = new DatabaseConnection();
			$database = $conn->getDatabase();
			$email = $_POST['email'];
			$pwd = $_POST['pwd'];
			$pwdConfirm = $_POST['pwd-confirm'];
			$name = $_POST['name'];
			$firstname = $_POST['firstname'];

			if ($pwd == $pwdConfirm) {
				$sql = $database->prepare(
					"INSERT INTO Account (email, password, id_person) 
					VALUES (:email, :pwd, (SELECT P.id_person FROM Person P WHERE last_name = :name AND first_name = :firstname))");
				$sql->bindParam(':email', $email);
				$sql->bindParam(':pwd', $pwd);
				$sql->bindParam(':name', $name);
				$sql->bindParam(':firstname', $firstname);
				$sql->execute();
				header('Location: ' . $router->url('home'));
			} else {
				$error = 'Les mots de passe ne correspondent pas';
			}

		} else {
			$error = 'Veuillez remplir tous les champs';
		}

		$this->render('signup.twig', ['router' => $router, 'error' => $error ?? '']);
	}
}