<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/error')]
class ErrorController extends AbstractController
{
    #[Route('/{code}', name: 'error')]
    public function index(int $code = 404): Response
    {
        switch ($code) {
            case 403:
                $message = 'accès refusé';
                break;
            case 404:
                $message = 'page non trouvée';
                break;
            case 500:
                $message = 'erreur serveur';
                break;
            default:
                return $this->redirectToRoute('error');
        }

        return $this->render(
            'error.html.twig',
            ['code' => $code, 'message' => $message],
        );
    }
}
