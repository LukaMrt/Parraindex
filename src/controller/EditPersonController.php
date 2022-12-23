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
    
            // throw error if user is not logged in
            if (empty($_SESSION)) {
                header('Location: ' . $router->url('error', ['error' => 403]));
                die();
            }
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

        $person = $this->personService->getPersonById($parameters['id']);
        $error = "";

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
            'color' => $person->getColor(),

		];

        $data['biography'] = $_POST['person-bio'] ?? null;
        $data['description'] = $_POST['person-desc'] ?? null;

        if (!isset($data['biography'], $data['description'])){
            $error .=  "<li> Les champs <strong>biographie</strong> et <strong>description</strong> sont indisponibles <br>";
        }
        

        if (isset($_POST['banner-color']) && preg_match('/^#[a-f0-9]{6}$/i', $_POST['banner-color'])){
            $data['color'] = $_POST['banner-color'];
        }else {
            $error .=  "<li> La couleur doit être au format <strong>exadecimal</strong> <br>";
        }

        if ($isAdmin){
            $data['first_name'] = $_POST['person-firstname'] ?? "";
            $data['last_name'] = $_POST['person-lastname'] ?? "";

            if ($data['first_name'] === "" || $data['last_name'] === ""){
                $error .=  "<li> Le <strong>nom</strong> et le <strong>prénom</strong> ne peuvent pas être vide <br>";
            }
        }
    
        $picture = $_FILES['person-picture'] ?? null;
        if (isset($picture) && $picture['tmp_name'] !== "") {

            $imgPath = "img/pictures/";
            $extensions = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);
            
            if (!in_array(exif_imagetype($picture['tmp_name']) , $extensions)){
                // throw an exception if the file is not an image
                $error .= "<li> Le fichier n'est pas une image prise en charge <br>";

            }else if ($picture['size']>5_000 * 1024){
                // throw an exception if the file is too big
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

        if ($parameters['id'] === "0") {
            //$this->personService->createPerson($data);
            throw new Exception("Not implemented yet");
        }

        $characteristics = $this->characteristicTypeService->getAllCharacteristicAndValues($person);

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
                
                if($exist) {
                    // the characteristic already exist, we just need to update it
                    $this->characteristicService->updateCharacteristic($parameters['id'], $characteristic);
                }else if ($characteristic->getValue() !== "") {
                    $this->characteristicService->createCharacteristic($parameters['id'], $characteristic);
                }

            }
        }

        $success = "";

        if (!$error) {
            $this->personService->updatePerson($data);
            $success = "Modifications enregistrées";
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
}