
// -------------------------------- functions --------------------------------- //

function checkInputValidity(element) {
	if (element.checkValidity()) {
		element.classList?.remove('form__element--invalid');
		return true;
	}
	element.classList.add('form__element--invalid');
	return false;
}

function addFieldsListeners() {
	fields.forEach(field => document.getElementById(field.name)
		.addEventListener('focusout', () => field.validation(document.getElementById(field.name))));
}

function createErrPopup(){
	let errorPopup = document.querySelector('.error-popup');
	let errorContent = document.querySelector('.error-popup__content');

	errorContent.replaceChildren();

	let errors = fields.filter(field => !field.validation(document.getElementById(field.name)))
					.map(field => field.error)
	
	errors.forEach(error => {
		let errorElement = document.createElement('p');
		errorElement.textContent = error;
		errorContent.appendChild(errorElement);
	});

	errorPopup.classList.add('error-popup--visible');
	setTimeout(() => errorPopup.classList.remove('error-popup--visible'), 5_000);

	if (errors.length){
		return true;
	}
	return false;
}

// ---------------------------------- Fields ---------------------------------- //

let fields = [
	{
		name: 'firstname',
		error: 'Le prénom doit contenir au moins 1 caractère',
		validation: checkInputValidity
	},
	{
		name: 'lastname',
		error: 'Le nom doit contenir au moins 1 caractère',
		validation: checkInputValidity
	},
	{
		name: 'description',
		error: 'La description doit contenir au moins 1 caractère',
		validation: checkInputValidity
	},
	{
		name: 'type',
		error: 'Le type doit être valide',
		validation: checkInputValidity
	},
	{
		name: 'email',
		error: 'L\'email doit être valide',
		validation: checkInputValidity
	}
];

// ------------------------------ Event Listener ------------------------------ //

let submitButton = document.querySelector('.btn[type="submit"]');
submitButton.addEventListener('click', (e) => {
	if (createErrPopup()){
		e.preventDefault();
	}
});

let closeErrorPopup = document.querySelector('.error-popup');

closeErrorPopup.addEventListener('click', () => {
	closeErrorPopup.classList.remove('error-popup--visible');
	console.log('click');

});

addFieldsListeners();