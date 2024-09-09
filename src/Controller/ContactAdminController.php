<?php

namespace App\Controller;

use App\Application\contact\ContactService;
use App\Application\person\PersonService;
use App\Entity\ContactType;
use App\Entity\Role;
use App\Infrastructure\old\router\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * the contact admin page, it's the page to view and manage all contact
 */
class ContactAdminController extends Controller
{
    /**
     * @var ContactService the contact service
     */
    private ContactService $contactService;


    /**
     * ContactAdminController constructor
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param ContactService $contactService the contact service
     * initialize the controller
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
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError when the template is not found
     * @throws RuntimeError when an error occurs during the rendering
     * @throws SyntaxError when an error occurs during the rendering
     */
    public function get(Router $router, array $parameters): void
    {

        if (empty($_SESSION) || Role::fromString($_SESSION['privilege']) !== Role::ADMIN) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        $list = $this->contactService->getContactList();
        $types = ContactType::getValues();

        $this->render('contactAdmin.html.twig', [
            'contacts' => $list,
            'typeContact' => $types
        ]);
    }
}
