<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Person\Person;
use App\Application\person\characteristic\CharacteristicService;
use App\Application\person\characteristic\CharacteristicTypeService;
use App\Application\person\PersonService;
use App\Entity\old\person\characteristic\Characteristic;
use App\Entity\old\person\PersonBuilder;
use App\Entity\Person\Role;
use App\Infrastructure\old\router\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The edit person page, it's the page to edit a person and his characteristics
 */
class EditPersonController extends Controller
{
    /**
     * @param Environment $twigEnvironment the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param CharacteristicTypeService $characteristicTypeService the characteristic type service
     * @param CharacteristicService $characteristicService the characteristic service
     */
    public function __construct(
        Environment $twigEnvironment,
        Router $router,
        PersonService $personService,
        private readonly CharacteristicTypeService $characteristicTypeService,
        private readonly CharacteristicService $characteristicService
    ) {
        parent::__construct($twigEnvironment, $router, $personService);
    }

    /**
     * @param Router $router the router
     * @param array<string, string> $parameters the parameters
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the rendering
     */
    #[\Override]
    public function get(Router $router, array $parameters): void
    {
        // if id is 0, create a new person
        if ($parameters['id'] === '0') {
            $person = PersonBuilder::aPerson()->build();
        } else {
            $person = $this->personService->getPersonById(intval($parameters['id']));

            // throw error if person does not exist
            if (!$person instanceof Person) {
                header('Location: ' . $router->url('error', ['error' => 404]));
                die();
            }
        }

        // throw error if user is not logged in
        if ($_SESSION === []) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        // @phpstan-ignore-next-line
        $isAdmin = Role::fromString($_SESSION['privilege']) === Role::ADMIN;

        // throw error if user is not admin or the person to edit is not the user
        if (!$isAdmin && $_SESSION['user']->getId() !== $person->getId()) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        $characteristicTypes = $this->characteristicTypeService->getAllCharacteristicTypes();

        $this->render(
            'editPerson.html.twig',
            // @phpstan-ignore-next-line
            [
                'person' => $person,
                'characteristics' => $characteristicTypes,
                'admin' => $isAdmin,
            ]
        );
    }


    /**
     * @param Router $router the router
     * @param array<string, string> $parameters the parameters
     */
    #[\Override] public function post(Router $router, array $parameters): void
    {
        header('content-type: Application/json');
        $json = file_get_contents('php://input');
        // @phpstan-ignore-next-line
        $data = json_decode($json, true);

        $response = [
            'code' => 200,
            'redirect' => false,
            'redirectDelay' => 0,
            'messages' => [],
        ];

        if ($_SESSION === []) {
            $response['code']       = 401;
            $response['messages'][] = "Vous devez être connecté et avoir les "
                . "droits d'administrateur pour ajouter une personne";
            echo json_encode($response);
            exit(0);
        }

        // @phpstan-ignore-next-line
        $isAdmin = Role::fromString($_SESSION['privilege']) === Role::ADMIN;
        if (!$isAdmin) {
            $response['code']       = 403;
            $response['messages'][] = "Vous n'avez pas les droits pour créer une personne";
            echo json_encode($response);
            exit(0);
        }

        // @phpstan-ignore-next-line
        $newValues = $this->getFormValues($data, $response, $isAdmin);
        // @phpstan-ignore-next-line
        $newCharacteristics = $this->getFormCharacteristics($data, $response);

        if ($response['code'] === 200) {
            if ($newValues['image']) {
                $imgPath = 'image/pictures/';
                file_put_contents($imgPath . $newValues['picture'], $newValues['image']);
            }

            // @phpstan-ignore-next-line
            $idPerson = $this->personService->createPerson($newValues);

            foreach ($newCharacteristics as $newCharacteristic) {
                if ($newCharacteristic->getValue() !== '') {
                    // @phpstan-ignore-next-line
                    $this->characteristicService->createCharacteristic($idPerson, $newCharacteristic);
                }
            }

            $response['messages'][] = "La personne a bien été créée à l'id " . $idPerson;
        }

        echo json_encode($response);
        exit(0);
    }


    /**
     * Get the form values
     * @param array<string, string> $data array the form data
     * @param array<string, string|string[]> $response the response if an error occurs
     * @param bool $isAdmin if the user is admin
     * @return array<string, string|null> the validated values
     */
    private function getFormValues(array $data, array &$response, bool $isAdmin): array
    {
        $newData = [];

        $newData['biography']   = $data['person-bio'] ?? null;
        $newData['description'] = $data['person-desc'] ?? null;

        if (!isset($newData['biography'], $newData['description'])) {
            // @phpstan-ignore-next-line
            $response['messages'][] = 'Les champs biographie et description sont indisponibles';
            $response['code']       = 400;
        }

        $newData['color'] = $data['banner-color'] ?? null;

        if (!isset($newData['color'])) {
            // @phpstan-ignore-next-line
            $response['messages'][] = 'La couleur de la bannière est indisponible';
            $response['code']       = 400;
        } elseif (
            preg_match(
                '/^#[a-f0-9]{6}$/i',
                $newData['color']
            ) === 0
            || preg_match(
                '/^#[a-f0-9]{6}$/i',
                $newData['color']
            ) === false
        ) {
            // @phpstan-ignore-next-line
            $response['messages'][] = 'La couleur doit être au format exadecimal';
            $response['code']       = 400;
        }

        // the picture in base64
        $newData['image'] = null;

        // the picture name
        $newData['picture'] = null;

        $encodedPictureData = $data['person-picture'] ?? null;

        if ($encodedPictureData === null) {
            // @phpstan-ignore-next-line
            $response['messages'][] = 'La photo est indisponible';
            $response['code']       = 400;
        } elseif (!empty($encodedPictureData)) {
            // regex to get the base64 encoded picture without the data:image/...;base64
            $encodedPicture = preg_replace('/^data:image\/\w+;base64,/', '', $encodedPictureData);

            $extensions = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
            ];

            $picture = base64_decode($encodedPicture ?? '');

            // @phpstan-ignore-next-line
            if ($picture === false) {
                // @phpstan-ignore-next-line
                $response['messages'][] = "La photo n'est pas correctement encodée";
                $response['code']       = 400;
            } else {
                $file = finfo_open();
                // @phpstan-ignore-next-line
                $mimeType = finfo_buffer($file, $picture, FILEINFO_MIME_TYPE);

                // @phpstan-ignore-next-line
                if (!array_key_exists($mimeType, $extensions)) {
                    // @phpstan-ignore-next-line
                    $response['messages'][] = "Le format de la photo n'est pas supporté";
                    $response['code']       = 400;
                } else {
                    $newData['image']   = $picture;
                    $newData['picture'] = uniqid() . 'controller' . $extensions[$mimeType];
                }
            }
        }

        if ($isAdmin) {
            $newData['first_name'] = $data['person-firstname'] ?? null;
            $newData['last_name']  = $data['person-lastname'] ?? null;

            if (!isset($newData['first_name'], $newData['last_name'])) {
                // @phpstan-ignore-next-line
                $response['messages'][] = 'Les champs prénom et nom sont indisponibles';
                $response['code']       = 400;
            } elseif ($newData['first_name'] === '' || $newData['last_name'] === '') {
                // @phpstan-ignore-next-line
                $response['messages'][] = 'Les champs prénom et nom ne peuvent pas être vides';
                $response['code']       = 400;
            }
        }

        return $newData;
    }


    /**
     * Get the form characteristics
     * @param array<string, string> $data array the data
     * @param array<string, string> $response the response if an error occurs
     * @return Characteristic[] the characteristics
     */
    private function getFormCharacteristics(array $data, array &$response): array
    {
        $newCharacteristics = [];

        $characteristics = $this->characteristicTypeService->getAllCharacteristicTypes();

        $characteristicsCounter = 0;

        foreach ($characteristics as $characteristic) {
            $fieldTitle      = 'characteristic-' . $characteristic->getTitle();
            $fieldVisibility = 'characteristic-visibility-' . $characteristic->getTitle();

            if (!isset($data[$fieldTitle])) {
                $response['messages'][] = 'Le champ ' . $characteristic->getTitle() . " n'est pas disponible";
                $response['code']       = 400;
            } else {
                $visibility = isset($data[$fieldVisibility]);

                if ($visibility) {
                    ++$characteristicsCounter;
                }

                if ($characteristicsCounter === 5) {
                    $response['messages'][] = 'Vous ne pouvez pas avoir plus de 4 caractéristiques visibles';
                    $response['code']       = 400;

                    // increment the counter to avoid the message to be displayed multiple times
                    ++$characteristicsCounter;
                }

                $characteristic->setValue($data[$fieldTitle]);
                $characteristic->setVisible($visibility);
                $newCharacteristics[] = $characteristic;
            }
        }

        return $newCharacteristics;
    }


    /**
     * @param Router $router the router
     * @param array<string, string> $parameters the parameters
     */
    #[\Override] public function put(Router $router, array $parameters): void
    {
        header('content-type: Application/json');
        $json = file_get_contents('php://input');
        // @phpstan-ignore-next-line
        $data = json_decode($json, true);

        $response = [
            'code' => 200,
            'redirect' => false,
            'redirectDelay' => 0,
            'messages' => [],
        ];

        if ($_SESSION === []) {
            $response['code']       = 401;
            $response['messages'][] = "Vous devez être connecté et avoir les droits "
                . "d'administrateur pour modifier une personne";
            echo json_encode($response);
            exit(0);
        }

        // @phpstan-ignore-next-line
        $person = $this->personService->getPersonById($parameters['id']);
        if (!$person instanceof Person) {
            $response['code']       = 404;
            $response['messages'][] = "La personne n'existe pas";
            echo json_encode($response);
            exit(0);
        }

        // @phpstan-ignore-next-line
        $isAdmin = Role::fromString($_SESSION['privilege']) === Role::ADMIN;
        if (!$isAdmin && $_SESSION['user']->getId() !== $person->getId()) {
            $response['code']       = 403;
            $response['messages'][] = "Vous n'avez pas les droits pour modifier cette personne";
            echo json_encode($response);
            exit(0);
        }

        // @phpstan-ignore-next-line
        $newValues = $this->getFormValues($data, $response, $isAdmin);

        $newValues['id']         = $person->getId();
        $newValues['first_name'] ??= $person->getFirstName();
        $newValues['last_name'] ??= $person->getLastName();

        // @phpstan-ignore-next-line
        $newCharacteristics = $this->getFormCharacteristics($data, $response);

        if ($response['code'] === 200) {
            if ($newValues['image']) {
                $imgPath = 'image/pictures/';
                if ($person->getPicture() != 'no-picture.svg' && file_exists($imgPath . $person->getPicture())) {
                    unlink($imgPath . $person->getPicture());
                }

                file_put_contents($imgPath . $newValues['picture'], $newValues['image']);
            } else {
                $newValues['picture'] = $person->getPicture();
            }

            $this->personService->updatePerson($newValues);

            // @phpstan-ignore-next-line
            $characteristicPerson = $this->characteristicTypeService->getAllCharacteristicAndValues($person);

            foreach ($newCharacteristics as $newCharacteristic) {
                $lastCharacteristic = array_filter(
                    $characteristicPerson,
                    fn($c): bool => $c->getId() === $newCharacteristic->getId()
                );

                // @phpstan-ignore-next-line
                $lastValue = array_shift($lastCharacteristic)->getValue();

                if ($lastValue !== null) {
                    // @phpstan-ignore-next-line
                    $this->characteristicService->updateCharacteristic($person->getId(), $newCharacteristic);
                } elseif ($newCharacteristic->getValue() !== '') {
                    // @phpstan-ignore-next-line
                    $this->characteristicService->createCharacteristic($person->getId(), $newCharacteristic);
                }
            }

            $response['messages'][] = 'Modifications correctement enregistrées';
        }

        echo json_encode($response);
        exit(0);
    }

    /**
     * @param array<string, string> $parameters
     */
    #[\Override] public function delete(Router $router, array $parameters): void
    {
        header('content-type: Application/json');

        $response = [
            'code' => 200,
            'redirect' => false,
            'redirectDelay' => 0,
            'messages' => [],
        ];

        if ($_SESSION === []) {
            $response['code']       = 401;
            $response['messages'][] = "Vous devez être connecté et avoir les droits d'administrateur "
                . "pour supprimer une personne";
            echo json_encode($response);
            exit(0);
        }

        // @phpstan-ignore-next-line
        $person = $this->personService->getPersonById($parameters['id']);
        if (!$person instanceof Person) {
            $response['code']       = 404;
            $response['messages'][] = 'La personne n°' . $parameters['id'] . " n'existe pas";
            echo json_encode($response);
            exit(0);
        }

        // @phpstan-ignore-next-line
        $isAdmin = Role::fromString($_SESSION['privilege']) === Role::ADMIN;
        if (!$isAdmin) {
            $response['code']       = 403;
            $response['messages'][] = 'Vous devez être Administrateur pour supprimer une personne';
            echo json_encode($response);
            exit(0);
        }

        $imgPath = 'image/pictures/';
        $img     = $person->getPicture();
        if ($img !== 'no-picture.svg' && file_exists($imgPath . $img)) {
            unlink($imgPath . $img);
        }

        $this->personService->deletePerson($person);

        $response['redirect']      = $router->url('tree');
        $response['redirectDelay'] = 5000;

        $response['messages'][] = 'La personne ' . $person->getFirstName() . ' '
            . strtoupper($person->getLastName() ?? '') . ' a correctement été supprimée';

        $response['messages'][] = "Vous allez être redirigé vers la page d'accueil dans "
            . ($response['redirectDelay'] / 1000) . 's';

        echo json_encode($response);
        exit(0);
    }
}
