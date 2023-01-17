
const contacts = [...document.querySelectorAll('.contact')];
const container = document.querySelector('.admin__contacts');
const filters = document.querySelector('.admin__filter__list');
const counter = document.querySelector('.admin__counter');

const actionButtons = document.querySelectorAll('.contact__actions-button');

function filterContacts() {

  const active = filters.querySelector('input[name="admin_filter"]:checked');
  let selected = []

  switch (active.value) {
    case 'all':
      selected = contacts.filter(contact => !contact.classList.contains('resolved'));
      break;

    case 'resolved':
      selected = contacts.filter(contact => contact.classList.contains('resolved'));
      break;

    default:
      selected = contacts.filter(contact => {
        if (contact.classList.contains('type-' + active.value) && !contact.classList.contains('resolved')) {
          return contact;
        }
      });
  }

  container.replaceChildren(...selected);

  counter.textContent = selected.length + ' ticket' + (selected.length > 1 ? 's' : '');

}

function buttonClick(event) {

  let accepted = true;

  switch (event.target.textContent) {
    case 'Supprimer':
      accepted = confirm("Confirmez vous la suppression ?");
      break;
    case 'Clore':
      accepted = confirm("Confirmez vous la fermeture de la demande de contact ?");
      break;
    case 'Créer':
      accepted = confirm("Confirmez vous la création de cette personne ?");
      break;
  }

  if (!accepted) {
    event.preventDefault();
  }
}

actionButtons.forEach(action => {
  action.addEventListener('click', buttonClick);
});

filters.addEventListener('change', filterContacts);

filterContacts();