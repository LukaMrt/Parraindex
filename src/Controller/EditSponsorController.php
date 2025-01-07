<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\person\PersonService;
use App\Application\sponsor\SponsorService;
use App\Entity\Person\Role;
use App\Infrastructure\old\router\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The edit sponsor page, it's the page to edit a sponsor
 */
class EditSponsorController extends Controller
{
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
        private readonly SponsorService $sponsorService
    ) {
        parent::__construct($twigEnvironment, $router, $personService);
    }


    /**
     * function get
     * @param Router $router the router
     * @param array<string, string> $parameters the parameters
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the rendering
     */
    #[\Override]
    public function get(Router $router, array $parameters): void
    {

        // @phpstan-ignore-next-line
        if ($_SESSION === [] || Role::fromString($_SESSION['privilege']) !== Role::ADMIN) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        // @phpstan-ignore-next-line
        $sponsor = $this->sponsorService->getSponsor($parameters['id']);
        $people  = $this->personService->getAllPeople();
        usort($people, fn($a, $b): int => $a->getLastName() !== '?' && $a->getLastName() < $b->getLastName() ? -1 : 1);
        $closure = fn($person): array => [
            'id' => $person->getId(),
            'title' => $person->getLastName() . ' EditSponsorController.php' . $person->getFirstName()
        ];
        $people  = array_map($closure, $people);
        $people2 = $people;

        $sponsorTypes = [
            ['id' => 0, 'title' => 'Parrainage IUT'],
            ['id' => 1, 'title' => 'Parrainage de coeur'],
            ['id' => 2, 'title' => 'Type inconnu']
        ];

        if ($sponsor !== null) {
            $godFather = $this->personService->getPersonById($sponsor->getGodFather()->getId());
            $godChild  = $this->personService->getPersonById($sponsor->getGodChild()->getId());
            $people    = [[
                // @phpstan-ignore-next-line
                'id' => $godFather->getId(),
                // @phpstan-ignore-next-line
                'title' => $godFather->getLastName() . ' EditSponsorController.php' . $godFather->getFirstName()
            ]];
            $people2   = [[
                // @phpstan-ignore-next-line
                'id' => $godChild->getId(),
                // @phpstan-ignore-next-line
                'title' => $godChild->getLastName() . ' EditSponsorController.php' . $godChild->getFirstName()
            ]];
            usort($sponsorTypes, fn($a, $b): int => $a['id'] == $sponsor->getTypeId() ? -1 : 1);
        }

        $this->render('editSponsor.html.twig', [
            'sponsor' => $sponsor,
            'people1' => $people,
            'people2' => $people2,
            'sponsorTypes' => $sponsorTypes
        ]);
    }


    /**
     * @param Router $router the router
     * @param array<string, string> $parameters the parameters
     */
    #[\Override] public function post(Router $router, array $parameters): void
    {

        // @phpstan-ignore-next-line
        if ($_SESSION === [] || Role::fromString($_SESSION['privilege']) !== Role::ADMIN) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        // @phpstan-ignore-next-line
        $sponsor = $this->sponsorService->getSponsor($parameters['id']);

        $formParameters = [
            'godFatherId' => $_POST['godFatherId'],
            'godChildId' => $_POST['godChildId'],
            'sponsorType' => $_POST['sponsorType'],
            'sponsorDate' => $_POST['sponsorDate'],
            'description' => $_POST['description'],
        ];

        if ($sponsor === null) {
            $this->sponsorService->createSponsor($formParameters);
        } else {
            $this->sponsorService->updateSponsor(intval($parameters['id']), $formParameters);
        }

        header('Location: ' . $router->url('sponsor', ['id' => $parameters['id']]));
        die();
    }
}
