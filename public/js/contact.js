import {Field, registerForm} from "./formValidator.js";

let defaultValidation = (element) => element.checkValidity();

let fields = [
	new Field('type', 'Le type doit être valide', defaultValidation),
	new Field('firstname', 'Le prénom doit contenir au moins 1 caractère', defaultValidation),
	new Field('lastname', 'Le nom doit contenir au moins 1 caractère', defaultValidation),
	new Field('email', 'L\'email doit être valide', defaultValidation),
	new Field('description', 'La description doit contenir au moins 1 caractère', (element) => element.value.trim().length > 0),
];

registerForm(document.querySelector('.form'), fields);
