<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\contact\ContactService;
use App\Application\login\SignupService;
use App\Application\person\PersonService;
use App\Application\sponsor\SponsorService;
use App\Entity\old\contact\PersonContact;
use App\Entity\Person\Role;
use App\Infrastructure\old\router\Router;
use Twig\Environment;

/**
 * The contact close page, it's the page to close a contact and execute the contact action
 */
class OldContactCloseController extends Controller
{
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
        private readonly ContactService $contactService,
        private readonly SponsorService $sponsorService,
        private readonly SignupService $signupService
    ) {
        parent::__construct($twigEnvironment, $router, $personService);
    }


    /**
     * @param Router $router the router
     * @param array<string, string> $parameters the parameters
     */
    #[\Override] public function get(Router $router, array $parameters): void
    {

        // @phpstan-ignore-next-line
        if ($_SESSION === [] || Role::fromString($_SESSION['privilege']) !== Role::ADMIN) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        if (!empty($parameters['resolve']) && $parameters['resolve'] === 'true') {
            $this->resolve(intval($parameters['id']));
        }

        $contactId  = $parameters['id'];
        $resolverId = $_SESSION['user']->getId();

        $this->contactService->closeContact(intval($contactId), $resolverId);

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

        if (!$contact instanceof PersonContact) {
            return;
        }

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
                // @phpstan-ignore-next-line
                $this->personService->deletePerson($contact->getPerson());
                break;

            case 3:
                // @phpstan-ignore-next-line
                $this->sponsorService->addSponsor($contact->getSponsor());
                break;

            case 5:
                // @phpstan-ignore-next-line
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
