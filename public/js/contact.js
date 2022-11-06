function checkInputValidity(element) {
	element.classList.add('form__element--invalid');
	if (element.value.length > 0) {
		element.classList.remove('form__element--invalid');
		return true;
	}
	return false;
}

function checkEmailValidity(emailInput) {
	emailInput.classList.add('form__element--invalid');
	if (emailInput.value.match(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/)) {
		emailInput.classList.remove('form__element--invalid');
		return true;
	}
	return false;
}

function addFieldsListeners() {

	fields.forEach(field => document.getElementById(field.name)
		.addEventListener('focusout', () => field.validation(document.getElementById(field.name))));
}

function addPopupCloseListener() {
	let errorPopup = document.getElementsByClassName('error-popup')[0];
	document.getElementsByClassName('error-popup__close-button')[0]
		.addEventListener('click', () => errorPopup.classList.remove('error-popup--visible'));
}

function performSubmit(e) {

	if (fields.every(field => field.validation(document.getElementById(field.name)))) {
		return;
	}

	e.preventDefault();

	let errorPopup = document.getElementsByClassName('error-popup')[0];
	errorPopup.getElementsByClassName("error-popup__content")[0].innerHTML = fields.filter(field => !field.validation(document.getElementById(field.name)))
		.map(field => field.error)
		.join('<br>');

	errorPopup.classList.add('error-popup--visible');
	setTimeout(() => errorPopup.classList.remove('error-popup--visible'), 5_000);
}

function addSubmitListener() {
	document.getElementsByClassName('btn')[0].addEventListener('click', (e) => performSubmit(e));
}

let fields = [
	{
		name: 'name',
		error: 'Le nom doit contenir au moins 1 caractères',
		validation: checkInputValidity
	},
	{
		name: 'description',
		error: 'La description doit contenir au moins 1 caractères',
		validation: checkInputValidity
	},
	{
		name: 'type',
		error: 'Le type doit contenir au moins 1 caractères',
		validation: checkInputValidity
	},
	{
		name: 'email',
		error: 'L\'email doit être valide',
		validation: checkEmailValidity
	}
];

addFieldsListeners();
addPopupCloseListener();
addSubmitListener();
