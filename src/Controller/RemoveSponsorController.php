<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\person\PersonService;
use App\Application\sponsor\SponsorService;
use App\Entity\Person\Role;
use App\Infrastructure\old\router\Router;
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
     * @param Environment $twigEnvironment the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param SponsorService $sponsorService the sponsor service
     */
    public function __construct(
        Environment $twigEnvironment,
        Router $router,
        PersonService $personService,
        SponsorService $sponsorService
    ) {
        parent::__construct($twigEnvironment, $router, $personService);
        $this->sponsorService = $sponsorService;
    }


    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     */
    #[NoReturn]
    #[\Override] public function get(Router $router, array $parameters): void
    {

        if ($_SESSION === [] || Role::fromString($_SESSION['privilege']) !== Role::ADMIN) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        $this->sponsorService->removeSponsor($parameters['id']);
        header('Location: ' . $router->url('home'));
        die();
    }
}
