
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

const controller = {
	nav: document.querySelector('.navbar'),

	searchIcon: document.querySelector('.navbar__icon--search'),
	filterIcon: document.querySelector('.navbar__icon--filter'),

	searchbar: document.querySelector('.navbar__searchbar'),
	filter: document.querySelector('.navbar__filter'),
}

const spinner = {
	up : document.querySelector('.spinner__up'),
	down : document.querySelector('.spinner__down'),

	dates : document.querySelectorAll('.spinner__dates'),
}

// --------------------- Functions --------------------- //

// relocate the slider to the specified position
function relocateSlider(percentage) {
	sliderUI.slider.scrollLeft = percentage * (sliderUI.slider.scrollWidth - sliderUI.slider.clientWidth)
	selectMiddleCard();
};

// relocate the scrollbar to match the slider position
function relocateScrollbar() {
	sliderUI.cursor.value = getHorizontalRatio() * 100;
	selectMiddleCard();
};

// get the current position of the slider
function getHorizontalRatio() {
	return sliderUI.slider.scrollLeft / (sliderUI.slider.scrollWidth - sliderUI.slider.clientWidth)
}

// give to the middle card the class "card--middle"
function selectMiddleCard (){
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

// construct an element with the specified tag and classes
function constructElement(tag, ...classNames) {
	const element = document.createElement(tag);

	element.classList.add('open');

	classNames.forEach((className) => {
		element.classList.add(className);
	});

	// console.log(element);
	return element;
};

// ---------------- Navigation Listeners --------------- //

// -- Drag events

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

// -- Range events

sliderUI.cursor.addEventListener("input", () => {
	relocateSlider(sliderUI.cursor.value / 100)
});

// -- Scroll events

sliderUI.slider.addEventListener("scroll", (event) => {
	relocateScrollbar();
});

// ---------------- Controller Listeners ---------------- //

// -- Searchbar events

controller.searchIcon.addEventListener('click', (e) => {
	controller.nav.classList.remove('navbar__filter--open');
	controller.filter.classList.add('navbar__filter--hidden');

	controller.nav.classList.toggle('navbar__searchbar--open')
	controller.searchbar.classList.toggle('navbar__searchbar--hidden');

});

// -- Filter events

controller.filterIcon.addEventListener('click', (e) => {
	controller.nav.classList.remove('navbar__searchbar--open');
	controller.searchbar.classList.add('navbar__searchbar--hidden');

	controller.nav.classList.toggle('navbar__filter--open');
	controller.filter.classList.toggle('navbar__filter--hidden');
});

// ---------------- Spinner Listeners ---------------- //

spinner.up.addEventListener('click', (e) => {
	spinner.dates.forEach(element => {
		element.textContent = parseInt(element.textContent) + 1;
	});
});

spinner.down.addEventListener('click', (e) => {
	spinner.dates.forEach(element => {
		element.textContent = parseInt(element.textContent) - 1;
	});
});

// ------------------ Initialization ----------------- //

relocateSlider(0.5)
relocateScrollbar();
