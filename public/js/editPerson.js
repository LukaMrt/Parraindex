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
	contacts : document.querySelector("card__social-network")
}

function initColor(){

	let bannerColor = getComputedStyle(preview.color).getPropertyValue("background-color")
	for (const color of field.colors.children){
		let selectorColor = getComputedStyle(color).getPropertyValue("background-color")
		if (selectorColor === bannerColor){
			color.children[0].checked = true;
		}
	}

}

function updatePreview(){
	preview.id.textContent = field.id.value;
	preview.bio.textContent = field.bio.value;

	preview.color.style.backgroundColor = field.colors.querySelector("input:checked").parentElement.style.backgroundColor;
}

initColor();

document.addEventListener("input", updatePreview);
