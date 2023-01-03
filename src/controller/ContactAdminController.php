<?php

namespace App\controller;

use App\application\contact\ContactService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use App\model\account\PrivilegeType;
use Twig\Environment;

class ContactAdminController extends Controller
{

    private ContactService $contactService;


    public function __construct(
        Environment    $twig,
        Router         $router,
        PersonService  $personService,
        ContactService $contactService
    )
    {
        parent::__construct($twig, $router, $personService);
        $this->contactService = $contactService;
    }


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
