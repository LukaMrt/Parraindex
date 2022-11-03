<?php

use App\infrastructure\database\DatabaseConnection;

include ("DatabaseConnection.php");
include ("../../../view/signup.twig");

$conn = new DatabaseConnection();
signUP($conn->getDatabase());


function signUP($database){
	if(isset($_POST['mail']) && isset($_POST['signup-pwd']) && isset($_POST['signup-pwd-confirm'])){
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
