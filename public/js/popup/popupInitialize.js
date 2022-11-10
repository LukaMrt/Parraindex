function closePopup(popup) {
	popup.classList.remove('popup--visible')
}

let errorPopup = document.querySelector('.popup--success');
let successPopup = document.querySelector('.popup--error');

errorPopup.querySelector('.popup__close-button')
	.addEventListener('click', () => closePopup(errorPopup));

successPopup.querySelector('.popup__close-button')
	.addEventListener('click', () => closePopup(successPopup));
