import {Field, registerForm} from "./formValidator.js";

let defaultValidation = (element) => element.checkValidity();

let fields = [
  new Field('email', 'L\'email doit être valide', defaultValidation),
];

registerForm(document.querySelector('.form'), fields);
