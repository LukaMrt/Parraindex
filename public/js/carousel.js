
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

// destroy an element
function destroyElement(input) {
	if (!input)
		return;

	for (const child of input.children) {
		child.classList.remove('open');
		child.classList.add('close');
	}

	input.classList.remove('open');
	input.classList.add('close');

	setTimeout(() => {
		input.remove();
	}, 300);
}

// open the searchbar
function openSearch() {
	let input = constructElement('input', 'searchbar--open', 'navbar__input');
	controller.searchbar.insertAdjacentElement('beforebegin', input);
};

// close the searchbar
function closeSearch() {
	let input = document.querySelector('.searchbar--open');
	controller.nav.classList.remove("navbar__searchbar--open");
	destroyElement(input);
};

// open the filters
function openFilter() {

	let filter = document.createElement('div');
	filter.classList.add('navbar__filters');

	for (let i = 0; i < 3; i++) {
		let button = document.createElement('button');

		button.classList.add('btn');
		if (i%2 == 1) {
			button.classList.add('btn--primary');
		}else{
			button.classList.add('btn--secondary');
		}
		button.classList.add('open');

		filter.appendChild(button);
	}

	controller.filter.insertAdjacentElement('afterend', filter);

};

//close the filters
function closeFilter() {
	let input = document.querySelector('.navbar__filters');
	controller.nav.classList.remove("navbar__filter--open");
	destroyElement(input);
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

controller.searchbar.addEventListener('click', (e) => {
	
	if(controller.nav.classList.toggle('navbar__searchbar--open')){
		closeFilter();
		openSearch();
	}else{
		closeSearch();
	}

});

// -- Filter events

controller.filter.addEventListener('click', (e) => {
	if(controller.nav.classList.toggle('navbar__filter--open')){
		closeSearch();
		openFilter();
	}else{
		closeFilter();
	}
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
