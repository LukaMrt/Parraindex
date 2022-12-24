import {triggerErrorPopup , triggerSuccessPopup} from "./popup/popups.js";

const field = {
	bio : document.querySelector("#bio-field"),
	about : document.querySelector("#about-field"),
	colors : document.querySelector("#color-field"),
	contact : document.querySelector("#contact-field"),
	picture : document.querySelector("#img-uploader"),
}

const preview = {
	bio : document.querySelector(".card__description"),
	color : document.querySelector(".card__banner"),
	contacts : document.querySelector("card__social-network"),
	picture : document.querySelector(".card__picture"),
	overlayPicture : document.querySelector(".card__picture__overlay"),
}

const pictureModal = {
	container : document.querySelector(".edit-picture"),
	imagePreview : document.querySelector(".edit-picture__preview__picture"),
	imageUploader : document.querySelector("#img-uploader"),
	closeButton : document.querySelector(".edit-picture__close"),
}

const initialPicture = preview.picture.src;
const deleteButton = document.querySelector("#delete-person");

function initColor(){

	let bannerColor = getComputedStyle(preview.color).getPropertyValue("background-color")
	pictureModal.imagePreview.style.backgroundColor = bannerColor;
	for (const color of field.colors.children){
		let selectorColor = getComputedStyle(color).getPropertyValue("background-color")
		if (selectorColor === bannerColor){
			color.children[0].checked = true;
		}
	}

}
/**
 * Update the card preview with the values of the form fields
 */
function updatePreview(){
	preview.bio.textContent = field.bio.value;

	let bannerColor = field.colors.querySelector("input:checked").parentElement.style.backgroundColor

	preview.color.style.backgroundColor = bannerColor;
	pictureModal.imagePreview.style.backgroundColor = bannerColor;
}

preview.picture.addEventListener("mouseover", function(){
	preview.overlayPicture.classList.add("overlay--active");
	preview.picture.classList.add("overlay--active");
})

preview.picture.addEventListener("mouseout", function(){
	preview.overlayPicture.classList.remove("overlay--active");
	preview.picture.classList.remove("overlay--active");
})


/**
 * Open the picture modal and add event listeners to close it
 */
preview.picture.addEventListener("click", (event) => {

	pictureModal.container.classList.add("edit-picture--active");
	event.stopPropagation();
	
	document.addEventListener("keydown", closeByKey);
	document.addEventListener("click", closeByClick);

	pictureModal.closeButton.addEventListener("click", e => {
		e.preventDefault();
		pictureModal.container.classList.remove("edit-picture--active");
		document.removeEventListener("keydown", closeByKey);
		document.removeEventListener("click", closeByClick);
	}, {once : true});

	function closeByKey(e){
		if (e.key === "Escape"){
			pictureModal.container.classList.remove("edit-picture--active");
			document.removeEventListener("keydown", closeByKey);
			document.removeEventListener("click", closeByClick);
		}
	}

	function closeByClick(e) {
		if (!e.path.includes(pictureModal.container)){
			pictureModal.container.classList.remove("edit-picture--active");
			document.removeEventListener("click", closeByClick);
		}
	}

});

/**
 * Syncronize the picture preview with the picture uploader
 */
field.picture.addEventListener("input", e => {

	let reader = new FileReader();
	let picture = field.picture.files[0];

	if (picture.size > 5_000 * 1024){

		// temporary alert, will be replaced by a modal
		alert("La taille de l'image est trop grande !");

		pictureModal.imageUploader.value = "";
		pictureModal.imagePreview.src = initialPicture;
		preview.picture.src = initialPicture;

		return;
	}
	
	reader.readAsDataURL(picture);
	reader.onload = function(e) {
		pictureModal.imagePreview.src = e.target.result;
		preview.picture.src = e.target.result;
	};
});


deleteButton?.addEventListener("click", async e => {

	e.preventDefault();
	
	if (confirm("Êtes-vous sûr de vouloir supprimer cette personne ?")){
		
		let request = await fetch(window.location.href, { method : "DELETE" });

		if (request.status !== 500){
			let response = await request.json();

			console.log(response);

			if (response.success){
				triggerSuccessPopup(response.messages);
				setTimeout(() => {
					window.location.href = response.redirect;
				}, response.redirectDelay);
			}else{
				triggerErrorPopup(response.messages);
			}
		}else{
			triggerErrorPopup(["Une erreur est survenue lors de la suppression de la personne"]);
		}
	}
});

initColor();

document.addEventListener("input", updatePreview);
