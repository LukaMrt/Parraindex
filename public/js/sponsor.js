// ------------------- Card clicks ------------------- //

let clickableCards = document.querySelectorAll(".card");
for (let card of clickableCards) {
    card.addEventListener('click', () => window.location.href = "/person/" + card.id);
}

let clickableNetworks = document.querySelectorAll(".social-network");
for (let network of clickableNetworks) {
    network.addEventListener('click', (e) => e.cancelBubble = true);
}
