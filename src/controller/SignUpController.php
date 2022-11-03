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
		$this->render('signup.twig');
		if(isset($_POST['mail']) && isset($_POST['signup-pwd']) && isset($_POST['signup-pwd-confirm'])){
			$conn = new DatabaseConnection();
			$database= $conn->getDatabase();
			$login = $_POST['signup'];
			$pwd = hash($_POST['signup-pwd'], PASSWORD_DEFAULT);
			$pwdConfirm = hash($_POST['signup-pwd-confirm'], PASSWORD_DEFAULT);
			if($pwd == $pwdConfirm){
				$sql = $database->prepare("INSERT INTO Account (email, password) VALUES (:login, :pwd)");
				$sql->bindParam(':login', $login);
				$sql->bindParam(':pwd', $pwd);
				$database->exec($sql);
				header('Location: /');
			}
			else{
				echo "Les mots de passe ne correspondent pas";
			}
		}
		else{
			echo "Veuillez remplir tous les champs";
		}
	}
}