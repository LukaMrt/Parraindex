<?php

namespace App\controller;

use App\application\person\characteristic\CharacteristicService;
use App\application\person\characteristic\CharacteristicTypeService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use App\model\account\PrivilegeType;
use App\model\person\PersonBuilder;
use JetBrains\PhpStorm\NoReturn;
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
     * @var CharacteristicTypeService the characteristic type service
     */
    private CharacteristicTypeService $characteristicTypeService;

    /**
     * @var CharacteristicService the characteristic service
     */
    private CharacteristicService $characteristicService;


    /**
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param CharacteristicTypeService $characteristicTypeService the characteristic type service
     * @param CharacteristicService $characteristicService the characteristic service
     */
    public function __construct(
        Environment $twig,
        Router $router,
        PersonService $personService,
        CharacteristicTypeService $characteristicTypeService,
        CharacteristicService $characteristicService
    ) {
        parent::__construct($twig, $router, $personService);
        $this->characteristicTypeService = $characteristicTypeService;
        $this->characteristicService = $characteristicService;
    }


    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the rendering
     */
    public function get(Router $router, array $parameters): void
    {
        // if id is 0, create a new person
        if ($parameters['id'] === '0') {
            $person = PersonBuilder::aPerson()->build();
        } else {
            $person = $this->personService->getPersonById($parameters['id']);

            // throw error if person does not exist
            if ($person === null) {
                header('Location: ' . $router->url('error', ['error' => 404]));
                die();
            }
        }

        // throw error if user is not logged in
        if (empty($_SESSION)) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        $isAdmin = PrivilegeType::fromString($_SESSION['privilege']) === PrivilegeType::ADMIN;

        // throw error if user is not admin or the person to edit is not the user
        if (!$isAdmin && $_SESSION['user']->getId() !== $person->getId()) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        $characteristicTypes = $this->characteristicTypeService->getAllCharacteristicTypes();

        $this->render(
            'editPerson.twig',
            [
                'person' => $person,
                'characteristics' => $characteristicTypes,
                'admin' => $isAdmin,
            ]
        );
    }


    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    #[NoReturn] public function post(Router $router, array $parameters): void
    {
        header('content-type: application/json');
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $response = [
            'code' => 200,
            'redirect' => false,
            'redirectDelay' => 0,
            'messages' => [],
        ];

        if (empty($_SESSION)) {
            $response['code'] = 401;
            $response['messages'][] = "Vous devez être connecté et avoir les "
                . "droits d'administrateur pour ajouter une personne";
            echo json_encode($response);
            exit(0);
        }

        $isAdmin = PrivilegeType::fromString($_SESSION['privilege']) === PrivilegeType::ADMIN;
        if (!$isAdmin) {
            $response['code'] = 403;
            $response['messages'][] = "Vous n'avez pas les droits pour créer une personne";
            echo json_encode($response);
            exit(0);
        }

        $newValues = $this->getFormValues($data, $response, $isAdmin);
        $newCharacteristics = $this->getFormCharacteristics($data, $response);

        if ($response['code'] === 200) {
            if ($newValues['image']) {
                $imgPath = 'img/pictures/';
                file_put_contents($imgPath . $newValues['picture'], $newValues['image']);
            }

            $idPerson = $this->personService->createPerson($newValues);

            foreach ($newCharacteristics as $characteristic) {
                if ($characteristic->getValue() !== '') {
                    $this->characteristicService->createCharacteristic($idPerson, $characteristic);
                }
            }

            $response['messages'][] = "La personne a bien été créée à l'id " . $idPerson;
        }

        echo json_encode($response);
        exit(0);
    }


    /**
     * Get the form values
     * @param $data array the form data
     * @param array $response the response if an error occurs
     * @param bool $isAdmin if the user is admin
     * @return array the validated values
     */
    private function getFormValues(array $data, array &$response, bool $isAdmin): array
    {
        $newData = [];

        $newData['biography'] = $data['person-bio'] ?? null;
        $newData['description'] = $data['person-desc'] ?? null;

        if (!isset($newData['biography'], $newData['description'])) {
            $response['messages'][] = 'Les champs biographie et description sont indisponibles';
            $response['code'] = 400;
        }

        $newData['color'] = $data['banner-color'] ?? null;

        if (!isset($newData['color'])) {
            $response['messages'][] = 'La couleur de la bannière est indisponible';
            $response['code'] = 400;
        } elseif (!preg_match('/^#[a-f0-9]{6}$/i', $newData['color'])) {
            $response['messages'][] = 'La couleur doit être au format exadecimal';
            $response['code'] = 400;
        }

        // the picture in base64
        $newData['image'] = null;

        // the picture name
        $newData['picture'] = null;

        $encodedPictureData = $data['person-picture'] ?? null;

        if ($encodedPictureData === null) {
            $response['messages'][] = 'La photo est indisponible';
            $response['code'] = 400;
        } elseif (!empty($encodedPictureData)) {
            // regex to get the base64 encoded picture without the data:image/...;base64
            $encodedPicture = preg_replace('/^data:image\/\w+;base64,/', '', $encodedPictureData);

            $extensions = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
            ];

            $picture = base64_decode($encodedPicture);

            if ($picture === false) {
                $response['messages'][] = "La photo n'est pas correctement encodée";
                $response['code'] = 400;
            } else {
                $file = finfo_open();
                $mimeType = finfo_buffer($file, $picture, FILEINFO_MIME_TYPE);

                if (!array_key_exists($mimeType, $extensions)) {
                    $response['messages'][] = "Le format de la photo n'est pas supporté";
                    $response['code'] = 400;
                } else {
                    $newData['image'] = $picture;
                    $newData['picture'] = uniqid() . '.' . $extensions[$mimeType];
                }
            }
        }

        if ($isAdmin) {
            $newData['first_name'] = $data['person-firstname'] ?? null;
            $newData['last_name'] = $data['person-lastname'] ?? null;

            if (!isset($newData['first_name'], $newData['last_name'])) {
                $response['messages'][] = 'Les champs prénom et nom sont indisponibles';
                $response['code'] = 400;
            } elseif ($newData['first_name'] === '' || $newData['last_name'] === '') {
                $response['messages'][] = 'Les champs prénom et nom ne peuvent pas être vides';
                $response['code'] = 400;
            }
        }

        return $newData;
    }


    /**
     * Get the form characteristics
     * @param $data array the data
     * @param array $response the response if an error occurs
     * @return array the characteristics
     */
    private function getFormCharacteristics(array $data, array &$response): array
    {
        $newCharacteristics = [];

        $characteristics = $this->characteristicTypeService->getAllCharacteristicTypes();

        $characteristicsCounter = 0;

        foreach ($characteristics as $characteristic) {
            $fieldTitle = 'characteristic-' . $characteristic->getTitle();
            $fieldVisibility = 'characteristic-visibility-' . $characteristic->getTitle();

            if (!isset($data[$fieldTitle])) {
                $response['messages'][] = 'Le champ ' . $characteristic->getTitle() . " n'est pas disponible";
                $response['code'] = 400;
            } else {
                $visibility = isset($data[$fieldVisibility]);

                if ($visibility) {
                    $characteristicsCounter++;
                }

                if ($characteristicsCounter === 5) {
                    $response['messages'][] = 'Vous ne pouvez pas avoir plus de 4 caractéristiques visibles';
                    $response['code'] = 400;

                    // increment the counter to avoid the message to be displayed multiple times
                    $characteristicsCounter++;
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
     * @param array $parameters the parameters
     * @return void
     */
    #[NoReturn] public function put(Router $router, array $parameters): void
    {
        header('content-type: application/json');
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $response = [
            'code' => 200,
            'redirect' => false,
            'redirectDelay' => 0,
            'messages' => [],
        ];

        if (empty($_SESSION)) {
            $response['code'] = 401;
            $response['messages'][] = "Vous devez être connecté et avoir les droits "
                . "d'administrateur pour modifier une personne";
            echo json_encode($response);
            exit(0);
        }

        $person = $this->personService->getPersonById($parameters['id']);
        if ($person === null) {
            $response['code'] = 404;
            $response['messages'][] = "La personne n'existe pas";
            echo json_encode($response);
            exit(0);
        }

        $isAdmin = PrivilegeType::fromString($_SESSION['privilege']) === PrivilegeType::ADMIN;
        if (!$isAdmin && $_SESSION['user']->getId() !== $person->getId()) {
            $response['code'] = 403;
            $response['messages'][] = "Vous n'avez pas les droits pour modifier cette personne";
            echo json_encode($response);
            exit(0);
        }

        $newValues = $this->getFormValues($data, $response, $isAdmin, $person);

        $newValues['id'] = $person->getId();
        $newValues['first_name'] = ($newValues['first_name'] ?? $person->getFirstName());
        $newValues['last_name'] = ($newValues['last_name'] ?? $person->getLastName());

        $newCharacteristics = $this->getFormCharacteristics($data, $response);

        if ($response['code'] === 200) {
            if ($newValues['image']) {
                $imgPath = 'img/pictures/';
                if ($person->getPicture() != 'no-picture.svg' && file_exists($imgPath . $person->getPicture())) {
                    unlink($imgPath . $person->getPicture());
                }

                file_put_contents($imgPath . $newValues['picture'], $newValues['image']);
            } else {
                $newValues['picture'] = $person->getPicture();
            }

            $this->personService->updatePerson($newValues);

            $characteristicPerson = $this->characteristicTypeService->getAllCharacteristicAndValues($person);

            foreach ($newCharacteristics as $characteristic) {
                $lastCharacteristic = array_filter(
                    $characteristicPerson,
                    function ($c) use ($characteristic) {
                        return $c->getId() === $characteristic->getId();
                    }
                );

                $lastValue = array_shift($lastCharacteristic)->getValue();

                if ($lastValue !== null) {
                    $this->characteristicService->updateCharacteristic($person->getId(), $characteristic);
                } elseif ($characteristic->getValue() !== '') {
                    $this->characteristicService->createCharacteristic($person->getId(), $characteristic);
                }
            }

            $response['messages'][] = 'Modifications correctement enregistrées';
        }

        echo json_encode($response);
        exit(0);
    }


    /**
     * @param Router $router
     * @param array $parameters
     * @return void
     */
    #[NoReturn] public function delete(Router $router, array $parameters): void
    {
        header('content-type: application/json');

        $response = [
            'code' => 200,
            'redirect' => false,
            'redirectDelay' => 0,
            'messages' => [],
        ];

        if (empty($_SESSION)) {
            $response['code'] = 401;
            $response['messages'][] = "Vous devez être connecté et avoir les droits d'administrateur "
                . "pour supprimer une personne";
            echo json_encode($response);
            exit(0);
        }

        $person = $this->personService->getPersonById($parameters['id']);
        if ($person === null) {
            $response['code'] = 404;
            $response['messages'][] = 'La personne n°' . $parameters['id'] . " n'existe pas";
            echo json_encode($response);
            exit(0);
        }

        $isAdmin = PrivilegeType::fromString($_SESSION['privilege']) === PrivilegeType::ADMIN;
        if (!$isAdmin) {
            $response['code'] = 403;
            $response['messages'][] = 'Vous devez être Administrateur pour supprimer une personne';
            echo json_encode($response);
            exit(0);
        }

        $imgPath = 'img/pictures/';
        $img = $person->getPicture();
        if ($img !== 'no-picture.svg' && file_exists($imgPath . $img)) {
            unlink($imgPath . $img);
        }

        $this->personService->deletePerson($person);

        $response['redirect'] = $router->url('tree');
        $response['redirectDelay'] = 5000;

        $response['messages'][] = 'La personne ' . $person->getFirstName() . ' '
            . strtoupper($person->getLastName()) . ' a correctement été supprimée';

        $response['messages'][] = "Vous allez être redirigé vers la page d'accueil dans "
            . ($response['redirectDelay'] / 1000) . 's';

        echo json_encode($response);
        exit(0);
    }
}
