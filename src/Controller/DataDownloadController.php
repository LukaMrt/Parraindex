<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Person\Role;
use App\Infrastructure\old\router\Router;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class DataDownloadController extends Controller
{
    #[\Override]
    public function get(Router $router, array $parameters): void
    {
        header('content-type: Application/json');

        $response = [
            'code' => 200,
            'content' => '',
            'messages' => [],
        ];

        if ($_SESSION === []) {
            $response['code']       = 401;
            $response['messages'][] = "Vous devez être connecté pour télécharger des données";
            echo json_encode($response);
            die();
        }

        $isAdmin = Role::fromString($_SESSION['privilege']) === Role::ADMIN;

        $id     = $_SESSION['user']->getId();
        $person = $this->personService->getPersonData($isAdmin ? $parameters['id'] : $id);

        if (!$person) {
            $response['code']       = 404;
            $response['messages'][] = "Aucune donnée n'a été trouvée";
        } else {
            $response['content']    = json_encode($person, JSON_PRETTY_PRINT);
            $response['messages'][] = "Données téléchargées";
        }

        echo json_encode($response);
        die();
    }
}
