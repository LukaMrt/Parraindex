import {Field, registerForm} from "./formValidator.js";
import {byId} from "./utils.js";

let defaultValidation = (element) => element.classList.contains('hidden') || element.checkValidity();

const identityField = {
  lastname : byId('contact[contacterFirstName]'),
  firstname : byId('contact[contacterLastName]'),
  email : byId('contact[contacterEmail]'),
}

const sessionIdentity = {
  lastname : identityField.lastname.value,
  firstname : identityField.firstname.value,
  email : identityField.email.value,
}

let fields = [
  new Field('contact[type]', 'Le type doit être valide', element => 0 <= element.value && element.value <= 9),
  new Field('contact[contacterFirstName]', 'Votre prénom doit contenir au moins 1 caractère', defaultValidation),
  new Field('contact[contacterLastName]', 'Votre nom doit contenir au moins 1 caractère', defaultValidation),
  new Field('contact[contacterEmail]', 'L\'email doit être valide', defaultValidation),
  new Field('contact[relatedPersonFirstName]', 'Le prénom doit contenir au moins 1 caractère', defaultValidation),
  new Field('contact[relatedPersonLastName]', 'Le nom doit contenir au moins 1 caractère', defaultValidation),
  new Field('contact[entryYear]', 'L\'année doit être valide', defaultValidation),
  new Field('contact[relatedPerson]', 'La personne doit être valide', defaultValidation),
  new Field('contact[relatedPerson2]', 'Le parrain doit être valide', defaultValidation),
  new Field('contact[relatedPerson2]', 'Le fillot doit être valide', defaultValidation),
  new Field('contact[sponsorType]', 'Le type de parrainage doit être valide', (element) => element.classList.contains('hidden') || 0 <= element.value && element.value <= 1),
  new Field('contact[sponsorDate]', 'La date doit être valide', defaultValidation),
  new Field('contact[password][first]', 'Le mot de passe doit contenir au moins 1 caractère', defaultValidation),
  new Field('contact[password][second]', 'Les mots de passe doivent être identiques', (element) => element.classList.contains('hidden') || element.value === byId('password').value),
  new Field('contact[description]', 'Le message doit contenir au moins 1 caractère', (element) => element.classList.contains('hidden') || element.value.trim().length > 0),
];

registerForm(document.querySelector('.form'), fields);

function updateClasses(id) {

  let syncSession = id !== '9' && identityField.lastname.value;

  identityField.lastname.value = syncSession ? sessionIdentity.lastname : '';
  identityField.firstname.value = syncSession ? sessionIdentity.firstname : '';
  identityField.email.value = syncSession ? sessionIdentity.email : '';

  identityField.lastname.readOnly = syncSession;
  identityField.firstname.readOnly = syncSession;
  identityField.email.readOnly = syncSession;

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

byId('contact[type]').addEventListener('change', (event) => updateClasses(event.target.value));

updateClasses(0);
