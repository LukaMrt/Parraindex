const constructElement = (tag, ...classNames) => {
	const element = document.createElement(tag);
	element.classList.add('open');

	classNames.forEach((className) => {
		element.classList.add(className);
	});

	return element;
};

const destroyElement = (input) => {
	if (!input)
		return;

	input.classList.remove('open');
	input.classList.add('close');

	setTimeout(() => {
		input.remove();
	}, 300);
}

const openSearch = () => {
	let input = constructElement('input', 'searchbar--open', 'navbar__input');
	controller.searchbar.insertAdjacentElement('beforebegin', input);
};

const closeSearch = () => {
	let input = document.querySelector('.searchbar--open');
	controller.nav.classList.remove("navbar__searchbar--open");
	destroyElement(input);
	
};

const openFilter = () => {
	let input = constructElement('input', 'filter--open', 'navbar__input');
	controller.filter.insertAdjacentElement('afterend', input);
};

const closeFilter = () => {
	let input = document.querySelector('.filter--open');
	controller.nav.classList.remove("navbar__filter--open");
	destroyElement(input);
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

controller.searchbar.addEventListener('click', (e) => {
	
	if(controller.nav.classList.toggle('navbar__searchbar--open')){
		closeFilter();
		openSearch();
	}else{
		closeSearch();
	}
});

controller.filter.addEventListener('click', (e) => {
	if(controller.nav.classList.toggle('navbar__filter--open')){
		closeSearch();
		openFilter();
	}else{
		closeFilter();
	}
});

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