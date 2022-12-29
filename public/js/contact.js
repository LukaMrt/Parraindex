import {Field, registerForm} from "./formValidator.js";

let defaultValidation = (element) => element.classList.contains('hidden') || element.checkValidity();

let fields = [
	new Field('type', 'Le type doit être valide', element => 0 <= element.value && element.value <= 8),
	new Field('senderFirstName', 'Votre prénom doit contenir au moins 1 caractère', defaultValidation),
	new Field('senderLastName', 'Votre nom doit contenir au moins 1 caractère', defaultValidation),
	new Field('senderEmail', 'L\'email doit être valide', defaultValidation),
	new Field('creationFirstName', 'Le prénom doit contenir au moins 1 caractère', defaultValidation),
	new Field('creationLastName', 'Le nom doit contenir au moins 1 caractère', defaultValidation),
	new Field('entryYear', 'L\'année doit être valide', defaultValidation),
	new Field('personId', 'La personne doit être valide', defaultValidation),
	new Field('godFatherId', 'Le parrain doit être valide', defaultValidation),
	new Field('godChildId', 'Le fillot doit être valide', defaultValidation),
	new Field('sponsorType', 'Le type de parrainage doit être valide', (element) => element.classList.contains('hidden') || 0 <= element.value && element.value <= 1),
	new Field('sponsorDate', 'La date doit être valide', defaultValidation),
	new Field('message', 'Le message doit contenir au moins 1 caractère', (element) => element.classList.contains('hidden') || element.value.trim().length > 0),
];

registerForm(document.querySelector('.form'), fields);

function updateClasses(id) {

	let elements = document.querySelectorAll('.option');

	for (let element of elements) {

		element.classList.remove('hidden');

		if (element.id !== 'bonusInformation') {
			element.required = true
		}

		let shouldHide = element.classList.contains('option-' + id) === false;
		if (shouldHide) {
			element.classList.add('hidden');
			element.required = false;
		}

	}

}

document.querySelector('#type').addEventListener('change', (event) => updateClasses(event.target.value));

updateClasses(0);
