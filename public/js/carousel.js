
// relocate the slider to the specified position
const relocateSlider = (pourcentage) => {
	sliderUI.slider.scrollLeft = pourcentage * (sliderUI.slider.scrollWidth - sliderUI.slider.clientWidth)
	selectMiddleCard();
};

// relocate the scrollbar to match the slider position
const relocateScrollbar = () => {
	sliderUI.cursor.value = getHorizontalRatio() * 100;
	selectMiddleCard();
};

// get the current position of the slider
const getHorizontalRatio = () => {
	return sliderUI.slider.scrollLeft / (sliderUI.slider.scrollWidth - sliderUI.slider.clientWidth)
}

// give to the middle card the class "card--middle"
const selectMiddleCard = () => {
	let index = getHorizontalRatio() * (sliderUI.cards.length - 1);
	let roundedIndex = Math.round(index);

	let diff = index - roundedIndex;

	sliderUI.middleCards.forEach(card => card.classList.remove('card--middle'));
	sliderUI.middleCards = [];

	if (Math.abs(diff) > 0.4 && roundedIndex > 0) {
		sliderUI.middleCards.push(sliderUI.cards[roundedIndex + Math.round(diff*2)]);
	}

	sliderUI.middleCards.push(sliderUI.cards[roundedIndex]);
	sliderUI.middleCards.forEach(card => card.classList.add("card--middle"));
}

// --------------------- constants --------------------- //

const sliderUI = {
	slider : document.querySelector(".carousel__slider"),
	cards : document.querySelectorAll(".card"),
	cursor : document.querySelector(".scrollbar__cursor"),
	middleCards : Array(),
}

const sliderController = {
	isMouseDown: false,
	scrollLeft: sliderUI.slider.offsetLeft,
	startPosX: 0,
};

// ------------------ Event Listeners ------------------ //

sliderUI.slider.addEventListener("mousedown", (event) => {
	sliderController.isMouseDown = true;
	sliderController.scrollLeft = sliderUI.slider.scrollLeft;
	sliderController.startPosX = event.pageX - sliderUI.slider.offsetLeft;
});

sliderUI.slider.addEventListener("mousemove", (event) => {
	if (!sliderController.isMouseDown)
		return;

	let diffX = event.pageX - sliderUI.slider.offsetLeft;
	let changeX = diffX - sliderController.startPosX;

	sliderUI.slider.scrollLeft = sliderController.scrollLeft - changeX;

	relocateScrollbar();
});

sliderUI.slider.addEventListener("mouseup", () => {
	sliderController.isMouseDown = false;
});

sliderUI.slider.addEventListener("mouseleave", () => {
	sliderController.isMouseDown = false;
});

sliderUI.cursor.addEventListener("input", () => {
	relocateSlider(sliderUI.cursor.value / 100)
});


sliderUI.slider.addEventListener("scroll", (event) => {
	relocateScrollbar();
});

// ------------------ Initialization ----------------- //

relocateSlider(0.5)
relocateScrollbar();
