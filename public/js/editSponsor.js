import {Field, registerForm} from "./formValidator.js";

let defaultValidation = (element) => element.classList.contains('hidden') || element.checkValidity();

let fields = [
	new Field('godFatherId', 'Le parrain doit être valide', defaultValidation),
	new Field('godChildId', 'Le fillot doit être valide', defaultValidation),
	new Field('sponsorType', 'Le type de parrainage doit être valide', (element) => 0 <= element.value && element.value <= 1),
	new Field('sponsorDate', 'La date doit être valide', defaultValidation),
];

registerForm(document.querySelector('.form'), fields);

function updateDescriptionTitle(value) {
	document.querySelector('#description-label').innerHTML = parseInt(value) === 0 ? 'Raison' : 'Description';
}

function remove(event) {
	if (!confirm("Confirmez vous la suppression ?")) {
		event.preventDefault();
	}
}

document.querySelector('#sponsorType').addEventListener('change', (event) => updateDescriptionTitle(event.target.value));
document.querySelector('.btn--danger').addEventListener('click', remove);
