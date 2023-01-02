import {Field, registerForm} from "./formValidator.js";
import {byId} from "./utils.js";

let defaultValidation = (element) => element.checkValidity();

let fields = [
  new Field('firstname', 'Le prénom doit contenir au moins 1 caractère', defaultValidation),
  new Field('lastname', 'Le nom doit contenir au moins 1 caractère', defaultValidation),
  new Field('email', 'L\'email doit être votre email universitaire', defaultValidation),
  new Field('password', 'Le mot de passe doit contenir au moins 1 caractère', defaultValidation),
  new Field('password-confirm', 'Les mots de passe doivent être identiques', (element) => element.value === byId('password').value),
];

registerForm(document.querySelector('.form'), fields);
