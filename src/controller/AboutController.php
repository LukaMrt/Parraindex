<?php

namespace App\controller;

use App\application\person\PersonService;
use App\infrastructure\router\Router;
use App\model\person\Identity;
use Twig\Environment;

class AboutController extends Controller
{
    public function __construct(Environment $twig, Router $router, PersonService $personService)
    {
        parent::__construct($twig, $router, $personService);
        $this->personService = $personService;
    }


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
