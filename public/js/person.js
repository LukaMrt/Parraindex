let wrapperSize = document.querySelector(".person-family__wrapper").clientHeight;

let godFathers = document.getElementsByClassName("person-family__godfather");
let count = godFathers.length + 1;

for (let i = 0; i < godFathers.length; i++) {

	let godFather = godFathers[i];
	let theta = (Math.PI) / count * (i + 1);

	let x = Math.round(50 + 50 * Math.cos(theta));
	let y = Math.round(50 - 50 * Math.sin(theta));
	let size = godFather.clientHeight;

	if (count > 5) {
		size = size * 0.5;
		godFather.style.height = size + "px";
		godFather.style.width = size + "px";
	}

	godFather.style.left = "calc(" + x + "%" + " - " + (size / 2) + "px)";
	godFather.style.top = "calc(" + y + "%" + " - " + (size / 2) + "px)";

}

let godChildren = document.getElementsByClassName("person-family__godchild");
count = godChildren.length + 1;

for (let i = 0; i < godChildren.length; i++) {

	let godChild = godChildren[i];
	let theta = (Math.PI) / count * (i + 1);

	let x = Math.round(50 + 50 * Math.cos(theta));
	let y = Math.round(50 - 50 * Math.sin(theta));
	let size = godChild.clientHeight;

	if (count > 5) {
		size = size * 0.5;
		godChild.style.height = size + "px";
		godChild.style.width = size + "px";
	}
	godChild.style.left = "calc(" + x + "%" + " - " + (size / 2) + "px)";
	godChild.style.bottom = "calc(" + y + "%" + " - " + (size / 2) + "px)";
}
