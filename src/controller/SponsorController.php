<?php

namespace App\controller;

use App\application\person\PersonService;
use App\application\sponsor\SponsorService;
use App\infrastructure\router\Router;
use Twig\Environment;

class SponsorController extends Controller
{
    private SponsorService $sponsorService;


    public function __construct(
        Environment    $twig,
        Router         $router,
        PersonService  $personService,
        SponsorService $sponsorService
    )
    {
        parent::__construct($twig, $router, $personService);
        $this->sponsorService = $sponsorService;
    }


    public function get(Router $router, array $parameters): void
    {
        $sponsor = $this->sponsorService->getSponsorById($parameters['id']);

        if ($sponsor === null) {
            header('Location: ' . $router->url('error', ['error' => 404]));
            die();
        }

        $this->render('sponsor.twig', [
            'sponsor' => $sponsor,
            'godFather' => $sponsor->getGodFather(),
            'godChild' => $sponsor->getGodChild(),
        ]);
    }
}
