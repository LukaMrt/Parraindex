<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\contact\ContactService;
use App\Application\login\SignupService;
use App\Application\person\PersonService;
use App\Application\sponsor\SponsorService;
use App\Entity\Person\Role;
use App\Infrastructure\old\router\Router;
use JetBrains\PhpStorm\NoReturn;
use Twig\Environment;

/**
 * The contact close page, it's the page to close a contact and execute the contact action
 */
class ContactCloseController extends Controller
{
    /**
     * @var ContactService the contact service
     */
    private ContactService $contactService;

    /**
         * @var SponsorService the sponsor service
     */
    private SponsorService $sponsorService;

    /**
     * @ver SignupService the signup service
     */
    private SignupService $signupService;


    /**
     * @param Environment $twigEnvironment the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param ContactService $contactService the contact service
     * @param SponsorService $sponsorService the sponsor service
     * @param SignupService $signupService the signup service
     */
    public function __construct(
        Environment $twigEnvironment,
        Router $router,
        PersonService $personService,
        ContactService $contactService,
        SponsorService $sponsorService,
        SignupService $signupService
    ) {
        parent::__construct($twigEnvironment, $router, $personService);
        $this->contactService = $contactService;
        $this->sponsorService = $sponsorService;
        $this->signupService  = $signupService;
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

        if (!empty($parameters['resolve']) && $parameters['resolve'] === 'true') {
            $this->resolve($parameters['id']);
        }

        $contactId  = $parameters['id'];
        $resolverId = $_SESSION['user']->getId();

        $this->contactService->closeContact($contactId, $resolverId);

        header('Location: ' . $router->url('contact_admin'));
        die();
    }


    /**
     * Resolves the contact action
     * @param int $id the contact id
     */
    private function resolve(int $id): void
    {

        $contact = $this->contactService->getContact($id);

        switch ($contact->getTypeId()) {
            case 0:
                $parameters = [
                    'first_name' => $contact->getPerson()->getFirstName(),
                    'last_name' => $contact->getPerson()->getLastName(),
                    'picture' => $contact->getPerson()->getPicture(),
                    'biography' => $contact->getPerson()->getBiography(),
                    'description' => $contact->getPerson()->getDescription(),
                    'color' => $contact->getPerson()->getColor(),
                    'start_year' => $contact->getPerson()->getStartYear(),
                ];
                $this->personService->createPerson($parameters);
                break;


            case 2:
                $this->personService->deletePerson($contact->getPerson());
                break;

            case 3:
                $this->sponsorService->addSponsor($contact->getSponsor());
                break;

            case 5:
                $this->sponsorService->removeSponsor($contact->getSponsor()->getId());
                break;

            case 9:
                $message = $contact->getMessage();
                $this->signupService->validate(substr($message, strrpos($message, '/') + 1));
                break;

            case 1:
            case 4:
            case 6:
            case 7:
            case 8:
            default:
                break;
        }
    }
}
