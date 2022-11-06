let nameInput = document.getElementById('name');
let descriptionInput = document.getElementById('description');
let typeInput = document.getElementById('type');
let emailInput = document.getElementById('email');
let submitButton = document.getElementsByClassName('btn')[0];
let errorPopup = document.getElementsByClassName('error-popup')[0];
let errorPopupClose = document.getElementsByClassName('error-popup__close-button')[0];

function checkInput(element) {
	element.classList.add('form__element--invalid');
	if (element.value.length > 0) {
		element.classList.remove('form__element--invalid');
		return true;
	}
	return false;
}

function checkEmail(emailInput) {
	emailInput.classList.add('form__element--invalid');
	if (emailInput.value.match(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/)) {
		emailInput.classList.remove('form__element--invalid');
		return true;
	}
	return false;
}

nameInput.addEventListener('focusout', () => checkInput(nameInput));
descriptionInput.addEventListener('focusout', () => checkInput(descriptionInput));
typeInput.addEventListener('focusout', () => checkInput(typeInput));
emailInput.addEventListener('focusout', () => checkEmail(emailInput));

errorPopupClose.addEventListener('click', () => errorPopup.classList.remove('error-popup--visible'));

submitButton.addEventListener('click', function (e) {

	if (checkInput(nameInput) && checkInput(descriptionInput) && checkInput(typeInput) && checkEmail(emailInput)) {
		return;
	}

	e.preventDefault();

	let message = 'Champs invalides : <br>';
	message += !checkInput(nameInput) ? 'nom, ' : '';
	message += !checkInput(descriptionInput) ? 'description, ' : '';
	message += !checkInput(typeInput) ? 'objet, ' : '';
	message += !checkEmail(emailInput) ? 'email' : '';

	errorPopup.getElementsByClassName("error-popup__content")[0].innerHTML = message;
	errorPopup.classList.add('error-popup--visible');
	setTimeout(() => errorPopup.classList.remove('error-popup--visible'), 3_000);
});
