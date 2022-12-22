const field = {
	id : document.querySelector("#id-field"),
	bio : document.querySelector("#bio-field"),
	about : document.querySelector("#about-field"),
	colors : document.querySelector("#color-field"),
	contact : document.querySelector("#contact-field"),
}

const preview = {
	id : document.querySelector(".card__identifiant"),
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
	preview.id.textContent = field.id.value;
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
pictureModal.imageUploader.addEventListener("change", e => {
	let reader = new FileReader();
	reader.readAsDataURL(pictureModal.imageUploader.files[0]);
	reader.onload = function(e) {
		pictureModal.imagePreview.src = e.target.result;
		preview.picture.src = e.target.result;
	};
});

initColor();

document.addEventListener("input", updatePreview);
