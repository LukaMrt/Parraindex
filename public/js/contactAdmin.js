function buttonClick(event) {

	if (event.target.innerHTML === "Supprimer" && !confirm("Confirmez vous la suppression ?")) {
		event.preventDefault();
	}

	if (event.target.innerHTML === "Clore" && !confirm("Confirmez vous la fermeture de la demande de contact ?")) {
		event.preventDefault();
	}

}

document.querySelectorAll('.contact__actions-button')
	.forEach(button => button.addEventListener('click', buttonClick));
