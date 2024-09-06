<?php

namespace App\Controller;

use App\Application\person\PersonService;
use App\Application\sponsor\SponsorService;
use App\Infrastructure\router\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The person page, it's the page where the user can see his person
 */
class PersonController extends Controller
{
    /**
     * @var SponsorService the sponsor service
     */
    private SponsorService $sponsorService;


    /**
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param SponsorService $sponsorService the sponsor service
     */
    public function __construct(
        Environment $twig,
        Router $router,
        PersonService $personService,
        SponsorService $sponsorService
    ) {
        parent::__construct($twig, $router, $personService);
        $this->sponsorService = $sponsorService;
    }


    /**
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the rendering
     */
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
