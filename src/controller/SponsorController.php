<?php

namespace App\controller;

use App\application\person\PersonService;
use App\application\sponsor\SponsorService;
use App\infrastructure\router\Router;
use Twig\Environment;

/**
 * class SponsorController
 * the sponsor page, it's the page where the user can see the sponsor
 */
class SponsorController extends Controller
{

    /**
     * @var SponsorService the sponsor service
     */
    private SponsorService $sponsorService;


    /**
     * SponsorController constructor
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param SponsorService $sponsorService the sponsor service
     * initialize the controller
     */
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


    /**
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
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
