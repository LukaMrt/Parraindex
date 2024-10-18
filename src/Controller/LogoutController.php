<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\login\LoginService;
use App\Application\person\PersonService;
use App\Infrastructure\old\router\Router;
use JetBrains\PhpStorm\NoReturn;
use Twig\Environment;

/**
 * The login page, it's the page where the user can log out
 */
class LogoutController extends Controller
{
    /**
     * @var LoginService the login service
     */
    private LoginService $loginService;


    /**
     * @param Environment $twigEnvironment the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param LoginService $loginService the login service
     */
    public function __construct(
        Environment $twigEnvironment,
        Router $router,
        PersonService $personService,
        LoginService $loginService
    ) {
        parent::__construct($twigEnvironment, $router, $personService);
        $this->loginService = $loginService;
    }


    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     */
    #[NoReturn]
    #[\Override]
    public function get(Router $router, array $parameters): void
    {
        $this->loginService->logout();
    }
}
