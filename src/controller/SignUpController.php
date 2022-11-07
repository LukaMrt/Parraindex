<?php

namespace App\controller;

use App\application\login\AccountDAO;
use App\infrastructure\router\Router;
use Twig\Environment;

class SignUpController extends Controller {

    private AccountDAO $accountDAO;

    public function __construct(Environment $twig, AccountDAO $accountDAO) {
        parent::__construct($twig);
        $this->accountDAO = $accountDAO;
    }

    public function get(Router $router, array $parameters): void {
        $this->render('signup.twig', ['router' => $router]);
    }

    public function post(Router $router, array $parameters): void {
        if (isset($_POST['email'], $_POST['password'], $_POST['password-confirm'])) {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $passwordConfirm = $_POST['password-confirm'];
            $name = $_POST['name'];
            $firstname = $_POST['firstname'];
            if ($password == $passwordConfirm) {
                $this->accountDAO->createAccount($email, $password, $name, $firstname);
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