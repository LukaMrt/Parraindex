let nameInput = document.getElementById('name');
let descriptionInput = document.getElementById('description');
let typeInput = document.getElementById('type');
let emailInput = document.getElementById('email');
let submitButton = document.getElementsByClassName('btn')[0];

function checkInput(element) {
	element.classList.add('form__element--invalid');
	if (element.value.length > 0) {
		element.classList.remove('form__element--invalid');
	}
}

function checkEmail(emailInput) {
	emailInput.classList.add('form__element--invalid');
	if (emailInput.value.match	(/^[\w-.]+@([\w-]+\.)+[\w-]{2,4}$/g)) {
		emailInput.classList.remove('form__element--invalid');
	}
}

nameInput.addEventListener('blur', () => checkInput(nameInput));
descriptionInput.addEventListener('blur', () => checkInput(descriptionInput));
typeInput.addEventListener('blur', () => checkInput(typeInput));
emailInput.addEventListener('blur', () => checkEmail(emailInput));

submitButton.addEventListener('click', function (e) {
	if (nameInput.value.length === 0 || descriptionInput.value.length === 0 || typeInput.value.length === 0) {
		e.preventDefault();
		checkInput(nameInput);
		checkInput(descriptionInput);
		checkInput(typeInput);
		checkEmail(emailInput);
	}
});
