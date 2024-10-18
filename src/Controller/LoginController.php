<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\login\LoginService;
use App\Application\person\PersonService;
use App\Infrastructure\old\router\Router;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['GET'])]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
