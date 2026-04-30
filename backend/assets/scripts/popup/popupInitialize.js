function closePopup(popup) {
  popup.classList.remove('popup--visible')
}

let errorPopup = document.querySelector('.popup--success');
let successPopup = document.querySelector('.popup--error');

if (errorPopup) {
  errorPopup.querySelector('.popup__close-button')
      .addEventListener('click', () => closePopup(errorPopup));
}

if (successPopup) {
    successPopup.querySelector('.popup__close-button')
        .addEventListener('click', () => closePopup(successPopup));
}
