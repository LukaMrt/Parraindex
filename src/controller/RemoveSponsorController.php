<?php

namespace App\controller;

use App\application\person\PersonService;
use App\application\sponsor\SponsorService;
use App\infrastructure\router\Router;
use App\model\account\PrivilegeType;
use JetBrains\PhpStorm\NoReturn;
use Twig\Environment;

class RemoveSponsorController extends Controller
{
    private SponsorService $sponsorService;

    public function __construct(Environment $twig, Router $router, PersonService $personService, SponsorService $sponsorService)
    {
        parent::__construct($twig, $router, $personService);
        $this->sponsorService = $sponsorService;
    }

    #[NoReturn] public function get(Router $router, array $parameters): void
    {

        if (empty($_SESSION) || PrivilegeType::fromString($_SESSION['privilege']) !== PrivilegeType::ADMIN) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        $this->sponsorService->removeSponsor($parameters['id']);
        header('Location: ' . $router->url('home'));
        die();
    }
}
