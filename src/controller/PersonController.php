<?php

namespace App\controller;

use App\application\person\PersonService;
use App\application\sponsor\SponsorService;
use App\infrastructure\router\Router;
use Twig\Environment;

class PersonController extends Controller
{
    private SponsorService $sponsorService;


    public function __construct(
        Environment $twig,
        Router $router,
        PersonService $personService,
        SponsorService $sponsorService
    ) {
        parent::__construct($twig, $router, $personService);
        $this->sponsorService = $sponsorService;
    }


    public function get(Router $router, array $parameters): void
    {

        $family = $this->sponsorService->getPersonFamily($parameters['id']);

        if ($family === null) {
            header('Location: ' . $router->url('error', ['error' => 404]));
            die();
        }

        $this->render('person.twig', [
            'person' => $family['person'],
            'godFathers' => $family['godFathers'],
            'godChildren' => $family['godChildren'],
            'characteristics' => $family['person']->getCharacteristics()
        ]);
    }
}
