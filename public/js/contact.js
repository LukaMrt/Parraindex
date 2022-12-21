import {Field, registerForm} from "./formValidator.js";

let defaultValidation = (element) => element.classList.contains('hidden') || element.checkValidity();

let fields = [
	new Field('type', 'Le type doit être valide', element => 0 <= element.value && element.value <= 8),
	new Field('senderFirstname', 'Votre prénom doit contenir au moins 1 caractère', defaultValidation),
	new Field('senderLastname', 'Votre nom doit contenir au moins 1 caractère', defaultValidation),
	new Field('senderEmail', 'L\'email doit être valide', defaultValidation),
	new Field('creationFirstname', 'Le prénom doit contenir au moins 1 caractère', defaultValidation),
	new Field('creationLastname', 'Le nom doit contenir au moins 1 caractère', defaultValidation),
	new Field('entryYear', 'L\'année doit être valide', defaultValidation),
	new Field('personId', 'La personne doit être valide', defaultValidation),
	new Field('godFatherId', 'Le parrain doit être valide', defaultValidation),
	new Field('godChildId', 'Le fillot doit être valide', defaultValidation),
	new Field('sponsorType', 'Le type de parrainage doit être valide', (element) => element.classList.contains('hidden') || 0 <= element.value && element.value <= 1),
	new Field('sponsorDate', 'La date doit être valide', defaultValidation),
	new Field('message', 'La description doit contenir au moins 1 caractère', (element) => element.value.trim().length > 0),
];

registerForm(document.querySelector('.form'), fields);

function updateClasses(id) {

	let elements = document.querySelectorAll('.option');

	elements.forEach((element) => element.classList.remove('hidden'));

	for (let element of elements) {

		let shouldHide = element.classList.contains('option-' + id) === false;
		if (shouldHide) {
			element.classList.add('hidden');
		}

	}

}

document.querySelector('#type').addEventListener('change', (event) => updateClasses(event.target.value));

updateClasses(0);
