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
		$this->render('login.twig');
	}

	public function post(Router $router, array $parameters): void {
		$this->render('login.twig');
		if(isset($_POST['login']) && isset($_POST['login-pwd'])){
			$conn = new DatabaseConnection();
			$database= $conn->getDatabase();
			$login = $_POST['login'];
			$pwd = $_POST['login-pwd']; #we must hash the password before sending it to the database
			$sql = $database->prepare("SELECT * FROM Account WHERE email = :login AND password = :pwd");
			$sql->bindParam(':login', $login);
			$sql->bindParam(':pwd', $pwd);
			$result = $database->query($sql);
			if($result->rowCount() == 1){
				$_SESSION['login'] = $login;
				header('Location: /');
			}
			else{
				echo "Mauvais identifiants";
			}
		}
		else{
			echo "Veuillez remplir tous les champs";
		}
	}

}