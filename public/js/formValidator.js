import {byId} from './utils.js';
import {triggerErrorPopup} from "./popup/popups.js";

export class Field {

  constructor(name, error = "Erreur", validation = (element) => element.value.trim().length > 0) {
    this.name = name;
    this.error = error;
    this.validation = validation;
  }

}

export function registerForm(element, fields) {
  fields.forEach(field => byId(field.name).addEventListener('focusout', () => checkInputValidity(field)));
  element.querySelector('.btn[type="submit"]').addEventListener('click', (event) => checkFormValidity(event, fields));
}

function checkFormValidity(event, fields) {

  let errors = fields.filter(field => !field.validation(byId(field.name)));

  errors.forEach(field => byId(field.name).classList.add('form__element--invalid'));

  errors = errors.map(field => field.error);

  if (errors.length !== 0) {
    event.preventDefault();
    triggerErrorPopup(errors);
  }

}

function checkInputValidity(field) {

  let element = byId(field.name);

  if (field.validation(element)) {
    element.classList.remove('form__element--invalid');
    return true;
  }

  element.classList.add('form__element--invalid');
  return false;
}
