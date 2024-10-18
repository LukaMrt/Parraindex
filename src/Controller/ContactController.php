<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\contact\ContactService;
use App\Application\person\PersonService;
use App\Entity\Contact\Type;
use App\Infrastructure\old\router\Router;
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
     * @param Environment $twigEnvironment the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param ContactService $contactService the contact service
     */
    public function __construct(
        Environment $twigEnvironment,
        Router $router,
        PersonService $personService,
        ContactService $contactService
    ) {
        parent::__construct($twigEnvironment, $router, $personService);
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
    #[\Override]
    public function post(Router $router, array $parameters): void
    {

        $formParameters = [
            'type' => htmlspecialchars($_POST['type']),
            'senderFirstName' => htmlspecialchars($_POST['senderFirstName']),
            'senderLastName' => htmlspecialchars($_POST['senderLastName']),
            'senderEmail' => htmlspecialchars($_POST['senderEmail']),
            'creationFirstName' => htmlspecialchars($_POST['creationFirstName']),
            'creationLastName' => htmlspecialchars($_POST['creationLastName']),
            'entryYear' => htmlspecialchars($_POST['entryYear']),
            'godFatherId' => htmlspecialchars($_POST['godFatherId']),
            'godChildId' => htmlspecialchars($_POST['godChildId']),
            'sponsorType' => htmlspecialchars($_POST['sponsorType']),
            'sponsorDate' => htmlspecialchars($_POST['sponsorDate']),
            'password' => htmlspecialchars($_POST['password']),
            'passwordConfirm' => htmlspecialchars($_POST['passwordConfirm']),
            'message' => htmlspecialchars($_POST['message']),
            'personId' => htmlspecialchars($_POST['personId']),
            'bonusInformation' => htmlspecialchars($_POST['bonusInformation'])
        ];

        $error = $this->contactService->registerContact($formParameters);

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
    #[\Override]
    public function get(Router $router, array $parameters): void
    {
        $people = $this->personService->getAllPeople();
        usort($people, fn($a, $b): int => $a->getFirstName() !== '?' && $a->getFirstName() < $b->getFirstName() ? -1 : 1);
        $closure = fn($person): array => [
            'id' => $person->getId(),
            'title' => ucfirst($person->getFirstName()) . ' ContactController.php' . strtoupper($person->getLastName())
        ];
        $people  = array_map($closure, $people);

        $this->render('contact.html.twig', [
            'options' => Type::getValues(),
            'sponsorTypes' => [['id' => 0, 'title' => 'Parrainage IUT'], ['id' => 1, 'title' => 'Parrainage de coeur']],
            'people' => $people,
            'error' => $parameters['error'] ?? [],
        ]);
    }
}
