<?php

namespace App\Controller;

use App\Application\person\PersonService;
use App\Application\sponsor\SponsorService;
use App\Entity\account\PrivilegeType;
use App\Infrastructure\router\Router;
use JetBrains\PhpStorm\NoReturn;
use Twig\Environment;

/**
 * The remove sponsor page, it's the page where the admin can remove a sponsor
 */
class RemoveSponsorController extends Controller
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
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    #[NoReturn] public function get(Router $router, array $parameters): void
    {

        if (empty($_SESSION) || PrivilegeType::fromString($_SESSION['privilege']) !== PrivilegeType::ADMIN) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        $this->sponsorService->removeSponsor($parameters['id']);
        header('Location: ' . $router->url('home'));
        die();
    }
}
