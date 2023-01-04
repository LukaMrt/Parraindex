<?php

namespace App\controller;

use App\application\contact\ContactService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use App\model\account\PrivilegeType;
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

        if (empty($_SESSION) || PrivilegeType::fromString($_SESSION['privilege']) !== PrivilegeType::ADMIN) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        $list = $this->contactService->getContactList();

        $this->render('contactAdmin.twig', ['contacts' => $list]);
    }
}
