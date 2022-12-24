<?php

namespace App\controller;

use App\application\person\PersonService;
use App\application\person\characteristic\CharacteristicTypeService;
use App\application\person\characteristic\CharacteristicService;
use App\infrastructure\router\Router;
use App\model\account\PrivilegeType;
use App\model\person\PersonBuilder;
use Twig\Environment;
use Exception;

class EditPersonController extends Controller {

    private CharacteristicTypeService $characteristicTypeService;
    private CharacteristicService $characteristicService;

    public function __construct(Environment $twig, Router $router, PersonService $personService, CharacteristicTypeService $characteristicTypeService, CharacteristicService $characteristicService) {
        parent::__construct($twig, $router, $personService);
        $this->characteristicTypeService = $characteristicTypeService;
        $this->characteristicService = $characteristicService;
    }

    public function get(Router $router, array $parameters): void {

        // if id is 0, create a new person
        if ($parameters['id'] === "0") {
            $person = PersonBuilder::aPerson()->build();

        }else{
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
        if ( !$isAdmin && $_SESSION['user']->getId() !== $person->getId()) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        $characteristicTypes = $this->characteristicTypeService->getAllCharacteristicTypes();
        
        $this->render('editPerson.twig', 
            [
            'person' => $person,
            'characteristics' => $characteristicTypes,
            'admin' => $isAdmin
            ]
        );
    }

	public function post(Router $router, array $parameters): void {

        $error = "";

        if ($parameters['id'] === "0") {
            $person = PersonBuilder::aPerson()->build();
        }else{
            $person = $this->personService->getPersonById($parameters['id']);

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
        if ( !$isAdmin && $_SESSION['user']->getId() !== $person->getId()) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

		$data = [
            'id' => $parameters['id'],
			'first_name' => $person->getFirstName(),
			'last_name' => $person->getLastName(),
            'picture' => $person->getPicture(),
            'color' => $person->getColor()
		];

        // --- Check if biography and description are set --- //

        $data['biography'] = $_POST['person-bio'] ?? null;
        $data['description'] = $_POST['person-desc'] ?? null;

        if (!isset($data['biography'], $data['description'])){
            $error .=  "<li> Les champs <strong>biographie</strong> et <strong>description</strong> sont indisponibles <br>";
        }
        
        // --- Check if color is set and valid --- //

        if (isset($_POST['banner-color']) && preg_match('/^#[a-f0-9]{6}$/i', $_POST['banner-color'])){
            $data['color'] = $_POST['banner-color'];
        }else {
            $error .=  "<li> La couleur doit être au format <strong>exadecimal</strong> <br>";
        }

        // --- Check if first and last name are set and the user is admin --- //

        if ($isAdmin){
            $data['first_name'] = $_POST['person-firstname'] ?? "";
            $data['last_name'] = $_POST['person-lastname'] ?? "";

            if ($data['first_name'] === "" || $data['last_name'] === ""){
                $error .=  "<li> Le <strong>nom</strong> et le <strong>prénom</strong> ne peuvent pas être vide <br>";
            }
        }
    
        // --- Check if picture is set and valid --- //

        $picture = $_FILES['person-picture'] ?? null;
        if (isset($picture) && $picture['tmp_name'] !== "") {

            $imgPath = "img/pictures/";
            $extensions = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);
            
            if (!in_array(exif_imagetype($picture['tmp_name']) , $extensions)){
                $error .= "<li> Le fichier n'est pas une image prise en charge <br>";

            }else if ($picture['size']>5_000 * 1024){
                $error .= "<li> Le fichier est trop volumineux, il doit faire moins de <strong>5Mo</strong> <br>";
            }

            else{
                $oldPicture = $person->getPicture();
                $newPicture = $parameters['id'] . "." . pathinfo($picture['name'], PATHINFO_EXTENSION);
    
                if ($person->getPicture() != "no-picture.svg" && file_exists($imgPath . $oldPicture)) {
                    unlink($imgPath . $oldPicture);
                }
    
                move_uploaded_file($picture['tmp_name'], $imgPath . $newPicture);
                
                $data['picture'] = $newPicture;
                $person->setPicture($newPicture);
            }
        }

        // --- Create person if id is 0 --- //

        if ($parameters['id'] === "0")
            $personId = $this->personService->createPerson($data);
        else
            $personId = $parameters['id'];


        $characteristics = $this->characteristicTypeService->getAllCharacteristicAndValues($person);

        // --- Check if characteristics are set and valid --- //

        foreach ($characteristics as $characteristic) {

            $fieldTitle = "characteristic-" . $characteristic->getTitle();
            $fieldVisibility = "characteristic-visibility-" . $characteristic->getTitle();

            if (!isset($_POST[$fieldTitle])) {
                $error .= "<li> Le champ <strong>" . $characteristic->getTitle() . "</strong> n'est pas disponible <br>";
            }

            if ($error){
                continue;
            }
            
            $NewValue = $_POST[$fieldTitle];
            $NewVisibility= isset($_POST[$fieldVisibility]);

            if ($NewValue !== $characteristic->getValue() || $NewVisibility !== $characteristic->getVisible()) {
                // an update is needed

                $exist = $characteristic->getValue() !== null;
                
                $characteristic->setValue($NewValue);
                $characteristic->setVisible($NewVisibility);

                //update the characteristic in the person
                foreach ($person->getCharacteristics() as $characteristicPerson) {
                    if ($characteristicPerson->getTitle() === $characteristic->getTitle()) {
                        $characteristicPerson->setValue($characteristic->getValue());
                        $characteristicPerson->setVisible($characteristic->getVisible());
                    }
                }
                
                if($exist) 
                    $this->characteristicService->updateCharacteristic($personId, $characteristic);
                else if ($characteristic->getValue() !== "")
                    $this->characteristicService->createCharacteristic( $personId, $characteristic);
            }
        }

        $success = "";

        if (!$error) {
            $this->personService->updatePerson($data);
            
            if ($parameters['id'] === "0"){
                $success = $data['first_name'] . " <strong>" . strtoupper($data['last_name']) . "</strong> à correctement été ajouté <br>";
                $success .= "<a href='/person/" . $personId . "'> Cliquez <strong>ici</strong> pour voir la fiche</a><br>";

                $person = PersonBuilder::aPerson()->build();
            }else{
                $success = "Modifications enregistrées";
            }
        } else {
            $error = "Les modifications n'ont pas été enregistrées : <br>" . $error;
        }
       
		$this->render('editperson.twig', [
            'success' => $success,
            'error' => $error,
            'person' => $person,
            'characteristics' => $characteristics,
            'admin' => $isAdmin
        ]);
	}

    public function delete(Router $router, array $parameters): void {

        header('content-type: application/json');

        $response = [
            'success' => false,
            'redirect' => false,
            'redirectDelay' => 0,
            'messages' => []
        ];

        if (empty($_SESSION)) {
            http_response_code(401);
            $response['messages'][] = "Vous devez être connecté et avoir les droits d'administrateur pour supprimer une personne";
            echo json_encode($response);
            exit(0);
        }
        
        $person = $this->personService->getPersonById($parameters['id']);

        if ($person === null) {
            http_response_code(404);
            $response['messages'][] = "La personne n°" . $parameters['id'] . " n'existe pas";
            echo json_encode($response);
            exit(0);
        }

        $isAdmin = PrivilegeType::fromString($_SESSION['privilege']) === PrivilegeType::ADMIN;
        
        if (!$isAdmin) {
            http_response_code(403);
            $response['messages'][] = "Vous devez être Administrateur pour supprimer une personne";
            echo json_encode($response);
            exit(0);
        }

        $suppress = $this->personService->deletePerson($person);

        if ($suppress) {
            http_response_code(200);
            $response['success'] = true;
            $response['redirect'] = $router->url('tree');
            $response['redirectDelay'] = 5000;

            $response['messages'][] = "La personne " . $person->getFirstName() . " ". strtoupper($person->getLastName()) . " à correctement été supprimée";
            $response['messages'][] = "Vous allez être redirigé vers la page d'accueil dans ". $response['redirectDelay']/1000 . "s";

            echo json_encode($response);
            exit(0);
        }

        http_response_code(500);
        exit(0);
    }
}