import {byClass} from "../utils.js";

function setPopupContent(type, messages) {

  let popup = byClass('popup--' + type);
  let popupContent = byClass('popup--' + type).querySelector('.popup__content');

  popupContent.replaceChildren();

  messages.forEach(message => {
    let errorElement = document.createElement('p');
    errorElement.textContent = message;
    popupContent.appendChild(errorElement);
  });

  popup.classList.add('popup--visible');
  setTimeout(() => popup.classList.remove('popup--visible'), 5_000);
}

export function triggerErrorPopup(messages) {
  setPopupContent('error', messages);
}

export function triggerSuccessPopup(messages) {
  setPopupContent('success', messages);
}
