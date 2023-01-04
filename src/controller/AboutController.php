<?php

namespace App\controller;

use App\application\person\PersonService;
use App\infrastructure\router\Router;
use App\model\person\Identity;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The about page, it's the page that explain the project and the team
 */
class AboutController extends Controller
{
    /**
     * AboutController constructor
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * initialize the controller
     */
    public function __construct(Environment $twig, Router $router, PersonService $personService)
    {
        parent::__construct($twig, $router, $personService);
        $this->personService = $personService;
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

        $authors = [
            $this->personService->getPersonByIdentity(new Identity("Lilian", "Baudry")),
            $this->personService->getPersonByIdentity(new Identity("Melvyn", "Delpree")),
            $this->personService->getPersonByIdentity(new Identity("Vincent", "Chavot Dambrun")),
            $this->personService->getPersonByIdentity(new Identity("Luka", "Maret"))
        ];

        $this->render('about.twig', ['authors' => $authors]);
    }
}
