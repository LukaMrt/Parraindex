import {Field, registerForm} from "./formValidator.js";

let defaultValidation = (element) => element.checkValidity();

let fields = [
	new Field('login', 'L\'email doit être valide', defaultValidation),
	new Field('password', 'Le mot de passe doit contenir au moins 1 caractère', defaultValidation),
];

registerForm(document.querySelector('.form'), fields);
