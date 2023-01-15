<?php

namespace App\controller;

use App\infrastructure\router\Router;
use App\model\account\PrivilegeType;

/**
 * Controller to download data
 */
class DataDownloadController extends Controller
{
    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the rendering
     */
    public function get(Router $router, array $parameters): void
    {
        header('content-type: application/json');

        $response = [
            'code' => 200,
            'content' => '',
            'messages' => [],
        ];

        if (empty($_SESSION)) {
            $response['code'] = 401;
            $response['messages'][] = "Vous devez être connecté pour téléchargé des données";
            echo json_encode($response);
            die();
        }

        $isAdmin = PrivilegeType::fromString($_SESSION['privilege']) === PrivilegeType::ADMIN;

        $id = $_SESSION['user']->getId();
        $person = $this->personService->getPersonData($isAdmin ? $parameters['id'] : $id);
        
        if (!$person) {
            $response['code'] = 404;
            $response['messages'][] = "Aucune donnée n'a été trouvée";
        } else {
            $response['content'] = json_encode($person, JSON_PRETTY_PRINT);
            $response['messages'][] = "Données téléchargées";
        }

        echo json_encode($response);
        die();
    }
}
