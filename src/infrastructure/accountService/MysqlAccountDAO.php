<?php

namespace App\infrastructure\accountService;

use App\application\AccountPassword;
use App\infrastructure\database\DatabaseConnection;
use PDO;

class MysqlAccountDAO
{
	private PDO $database;

	public function __construct(){
		$conn= new DatabaseConnection();
		$this->database = $conn->getDatabase();
	}

	public function getAccount($login){
		$database = $this->database;
		$account = $database->prepare("SELECT * FROM Account WHERE email = :login");
		$account->bindParam(':login', $login);
		$account->execute();
		$result= $account->fetch();
		$database = null;
		return $result;
	}

	public function createAccount($router,$email,$password,$name,$firstname) : void {
		$database = $this->database;
		$sql = $database->prepare(
			"INSERT INTO Account (email, password, id_person) 
			VALUES (:email, :password, (SELECT P.id_person FROM Person P WHERE last_name = :name AND first_name = :firstname))");
		$sql->bindParam(':email', $email);
		$password = new  AccountPassword($password);
		$password->hashPassword();
		$password = $password->getPassword();
		$sql->bindParam(':password', $password);
		$sql->bindParam(':name', $name);
		$sql->bindParam(':firstname', $firstname);
		$sql->execute();
		header('Location: ' . $router->url('home'));
		$database = null;
	}
}