// relocate the slider to the specified position
const relocate = (element, pourcentage) =>{
	element.scrollLeft = pourcentage * (element.scrollWidth - element.clientWidth)
};

let slider = document.querySelector(".carousel__slider");

let sliderController = {
	isMouseDown: false,
	scrollLeft: slider.offsetLeft,
	startPosX: 0,
};

slider.addEventListener("mousedown", (event) => {
	sliderController.isMouseDown = true;
	sliderController.scrollLeft = slider.scrollLeft;
	sliderController.startPosX = event.pageX - slider.offsetLeft;
});

slider.addEventListener("mousemove", (event) => {
	if (!sliderController.isMouseDown)
		return;

	let diffX = event.pageX - slider.offsetLeft;
	let changeX = diffX - sliderController.startPosX;
	slider.scrollLeft = sliderController.scrollLeft - changeX;
});

slider.addEventListener("mouseup", (event) => {
	sliderController.isMouseDown = false;
});

slider.addEventListener("mouseleave", (event) => {
	sliderController.isMouseDown = false;
});

relocate(slider, 0.5)