import {Field, registerForm} from "./formValidator.js";
import {byId} from "./utils.js";

let defaultValidation = (element) => element.checkValidity();

let fields = [
  new Field('registration_form[email]', 'L\'email doit être votre email universitaire', defaultValidation),
  new Field('registration_form[password][first]', 'Le mot de passe doit contenir au moins 1 caractère', defaultValidation),
  new Field('registration_form[password][second]', 'Les mots de passe doivent être identiques', (element) => element.value === byId('password').value),
];

let form = document.querySelector('.form');
while (!form) {
  setTimeout(() => form = document.querySelector('.form'), 100);
}

registerForm(form, fields);
