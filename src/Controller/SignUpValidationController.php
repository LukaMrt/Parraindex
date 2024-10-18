<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\login\SignupService;
use App\Application\person\PersonService;
use App\Infrastructure\old\router\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The signup validation page, it's the page where the user can validate his signup
 */
class SignUpValidationController extends Controller
{
    /**
     * @var SignupService the signup service
     */
    private SignupService $signupService;


    /**
     * @param Environment $twigEnvironment the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param SignupService $signupService the password service
     */
    public function __construct(
        Environment $twigEnvironment,
        Router $router,
        PersonService $personService,
        SignupService $signupService
    ) {
        parent::__construct($twigEnvironment, $router, $personService);
        $this->signupService = $signupService;
    }


    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     * @throws LoaderError if the template cannot be found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the rendering
     */
    #[\Override]
    public function get(Router $router, array $parameters): void
    {

        $error = $this->signupService->validate($parameters['token']);

        $this->render('signupValidation.html.twig', ['error' => $error]);
    }
}
