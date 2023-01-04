<?php

namespace App\controller;

use App\application\contact\ContactService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use App\model\contact\ContactType;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The contact page, it's the page to contact the team and the admin
 */
class ContactController extends Controller
{
    /**
     * @var ContactService the contact service
     */
    private ContactService $contactService;


    /**
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param ContactService $contactService the contact service
     */
    public function __construct(
        Environment $twig,
        Router $router,
        PersonService $personService,
        ContactService $contactService
    ) {
        parent::__construct($twig, $router, $personService);
        $this->contactService = $contactService;
    }


    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void the function return nothing
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the rendering
     */
    public function post(Router $router, array $parameters): void
    {

        // TODO : create new array with htmlspecialchars
        $error = $this->contactService->registerContact($_POST);

        $this->get($router, ['error' => $error]);
    }


    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void the function return nothing
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the rendering
     */
    public function get(Router $router, array $parameters): void
    {

        $people = $this->personService->getAllPeople();
        usort($people, fn($a, $b) => $a->getLastName() !== '?' && $a->getLastName() < $b->getLastName() ? -1 : 1);
        $closure = fn($person) => [
            'id' => $person->getId(),
            'title' => $person->getLastName() . ' ' . $person->getFirstName()
        ];
        $people = array_map($closure, $people);

        $this->render('contact.twig', [
            'options' => ContactType::getValues(),
            'sponsorTypes' => [['id' => 0, 'title' => 'Parrainage IUT'], ['id' => 1, 'title' => 'Parrainage de coeur']],
            'people' => $people,
            'error' => $parameters['error'] ?? [],
        ]);
    }
}
