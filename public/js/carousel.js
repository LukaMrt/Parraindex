
// --------------------- constants --------------------- //

const sliderUI = {
	slider : document.querySelector(".carousel__slider"),
	cards : document.querySelectorAll(".card"),
	cursor : document.querySelector(".scrollbar__cursor"),
	min : document.querySelector(".scrollbar__min"),
	max : document.querySelector(".scrollbar__max"),
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

	searchbar: document.querySelector('.searchbar'),
	filter: document.querySelector('.filter')
}

const spinner = {
	up : document.querySelector('.spinner__up'),
	down : document.querySelector('.spinner__down'),

	datesContainer : document.querySelector('.spinner__container-date'),
	dates : document.querySelectorAll('.spinner__dates'),
}

const filter = {
	name: controller.searchbar.querySelector('.name_filter'),
	date: document.querySelector('.spinner__dates--start'),
	alpha: controller.filter.querySelector('.alpha_filter'),
}

// --------------------- Functions --------------------- //

// relocate the slider to the specified position
function relocateSlider(percentage) {
	sliderUI.slider.scrollLeft = percentage * (sliderUI.slider.scrollWidth - sliderUI.slider.clientWidth)
	selectMiddleCard();
}

// relocate the scrollbar to match the slider position
function relocateScrollbar() {
	sliderUI.cursor.value = getHorizontalRatio() * 100;
	selectMiddleCard();
}

// get the current position of the slider
function getHorizontalRatio() {
	return sliderUI.slider.scrollLeft / (sliderUI.slider.scrollWidth - sliderUI.slider.clientWidth)
}

// give to the middle card the class "card--middle"
function selectMiddleCard (){

	let cards = document.querySelectorAll('.card:not(.card--empty)')

	if (cards.length === 0) {
		sliderUI.min.textContent = "0";
		sliderUI.max.textContent = "0";
		return;
	}

	sliderUI.min.textContent = "1";
	sliderUI.max.textContent = cards.length;

	let index = getHorizontalRatio() * (cards.length - 1);
	let roundedIndex = Math.round(index);

	let diff = index - roundedIndex;

	sliderUI.middleCards.forEach(card => card?.classList.remove('card--middle'));
	sliderUI.middleCards = [];

	if (Math.abs(diff) > 0.4 && roundedIndex > 0) {
		sliderUI.middleCards.push(cards[roundedIndex + Math.round(diff*2)]);
	}

	sliderUI.middleCards.push(cards[roundedIndex]);
	sliderUI.middleCards.forEach(card => card.classList.add("card--middle"));
}

// filter the cards
function filterElements() {

	let cards = [];

	let selectedYear = document.querySelector(".spinner__dates--start").textContent;
	let filterByYear = !isNaN(selectedYear);
	let filterByName = controller.searchbar.classList.contains('searchbar--open')
		|| window.getComputedStyle(controller.searchIcon, null).display === "none";
	let filterByAlpha = filter.alpha.classList.contains('btn--primary');

	for (const card of sliderUI.cards) {

		let cardName = card.querySelector(".card__last-name").textContent.toLowerCase();
		cardName += " " + card.querySelector(".card__first-name").textContent.toLowerCase();
		let searchbarValue = filter.name.value.toLowerCase();

		let startYear = card.querySelector(".card__start-year").textContent;
		
		if (filterByYear && filterByName) {
			if (startYear === selectedYear && cardName.includes(searchbarValue)) {
				cards.push(card);
			}
		}
		else if (filterByYear){
			if (startYear === selectedYear) {
				cards.push(card);
			}
		}
		else if (filterByName){
			if (cardName.includes(searchbarValue)){
				cards.push(card);
			}
		}else{
			cards.push(card);
		}

	}

	if (filterByAlpha){
		cards.sort(function (a, b) {
			let aName = a.querySelector(".card__last-name").textContent.toLowerCase();
			let bName = b.querySelector(".card__last-name").textContent.toLowerCase();
	
			if (aName < bName) {
				return -1;
			}
			if (aName >  bName) {
				return 1;
			}
			return 0;
		});
	}

	sliderUI.slider.replaceChildren(...cards);

	if (cards.length === 0) {
		let emptyCard = document.createElement("div");
		emptyCard.classList.add("card");
		emptyCard.classList.add("card--empty");
		emptyCard.classList.add("card--middle");
		emptyCard.textContent = "Aucun résultat trouvé";
		sliderUI.slider.appendChild(emptyCard);
	}

	if (filterByAlpha){
		relocateSlider(0);
	}else{
		relocateSlider(0.5)
	}
}

// reset the spinner
function resetSpinner(){
	spinner.dates.forEach(element => {
		element.textContent = "—-—";
	});
	filterElements();
}

// update the spinner
function updateSpinnerDate(number){
	if(isNaN(spinner.dates[0].textContent) || isNaN(spinner.dates[1].textContent)){
		let date = new Date();
		spinner.dates[0].textContent = date.getFullYear();
		spinner.dates[1].textContent = date.getFullYear() + 1;
		filterElements();
		return;
	}

	spinner.dates.forEach(element => {
		element.textContent = parseInt(element.textContent) + number;
	});
	filterElements();
}

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

sliderUI.slider.addEventListener("scroll", () => {
	relocateScrollbar();
});

// ---------------- Controller Listeners ---------------- //

// -- Searchbar events

controller.searchIcon.addEventListener('click', () => {
	if (controller.nav.classList.toggle('navbar__searchbar--open')) {
		filter.name.focus();
	}

	controller.searchbar.classList.toggle('searchbar--open');
	filterElements();
});

// -- Filter events

controller.filterIcon.addEventListener('click', () => {
	controller.nav.classList.toggle('navbar__filter--open');
	controller.filter.classList.toggle('filter--open');
});

filter.name.addEventListener('input', () => {
	filterElements();
});

for (const filter of controller.filter.children) {
	filter.addEventListener('click', () => {
		filter.classList.toggle('btn--primary');
		filter.classList.toggle('btn--secondary');
		filterElements();
	});
}

for (let card of sliderUI.cards) {
	card.addEventListener('mouseenter', () => card.classList.add('card--hover'));
	card.addEventListener('mouseleave', () => card.classList.remove('card--hover'));
	card.addEventListener('click', () => window.location.href = "/person/" + card.id);
}

// ---------------- Spinner Listeners ---------------- //


spinner.up.addEventListener('click', () => {
	updateSpinnerDate(1)
});
spinner.down.addEventListener('click', () => {
	updateSpinnerDate(-1)
});
spinner.datesContainer.addEventListener('click', resetSpinner);


// ------------------ Initialization ----------------- //

relocateSlider(0.5)
relocateScrollbar();
resetSpinner();
