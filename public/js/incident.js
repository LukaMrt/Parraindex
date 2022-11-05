let nameInput = document.getElementById('name');
let descriptionInput = document.getElementById('description');
let typeInput = document.getElementById('type');
let submitButton = document.getElementsByClassName('btn')[0];

function checkInput(element) {
	element.classList.add('form__element--invalid');
	if (element.value.length > 0) {
		element.classList.remove('form__element--invalid');
	}
}

nameInput.addEventListener('blur', () => checkInput(nameInput));
descriptionInput.addEventListener('blur', () => checkInput(descriptionInput));
typeInput.addEventListener('blur', () => checkInput(typeInput));

submitButton.addEventListener('click', function (e) {
	if (nameInput.value.length === 0 || descriptionInput.value.length === 0 || typeInput.value.length === 0) {
		e.preventDefault();
		checkInput(nameInput);
		checkInput(descriptionInput);
	}
});
