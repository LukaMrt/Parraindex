<?php

use App\infrastructure\database\DatabaseConnection;

include ("DatabaseConnection.php");
include ("../../../view/login.twig");

$conn = new DatabaseConnection();
login($conn->getDatabase());


function login($database){
	if(isset($_POST['login']) && isset($_POST['login-pwd'])){
		$login = $_POST['login'];
		$pwd = hash($_POST['login-pwd'], PASSWORD_DEFAULT);
		$sql = $database->prepare("SELECT * FROM Account WHERE email = :login AND password = :pwd");
		sql->bindParam(':login', $login);
		sql->bindParam(':pwd', $pwd);
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

