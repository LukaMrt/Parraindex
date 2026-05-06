// Mock data for Parraindex
const PROMO_COLORS = {
  2019: '#2E4057',
  2020: '#48BFA0',
  2021: '#E85D75',
  2022: '#F4A236',
  2023: '#6C63FF',
  2024: '#2196F3',
  2025: '#8BC34A',
};

const FIRST_NAMES = [
  'Henri','Camille','Baptiste','Lilian','Marine','Thomas','Pauline','Maxime',
  'Léa','Antoine','Clara','Julien','Emma','Lucas','Manon','Hugo',
  'Chloé','Nathan','Sarah','Théo','Alice','Romain','Inès','Alexandre',
  'Louise','Adrien','Margaux','Enzo','Jade','Raphaël','Zoé','Gabriel',
  'Lina','Arthur','Eva','Louis','Ambre','Paul','Anaïs','Victor',
  'Lucie','Ethan','Mathilde','Tom','Charlotte','Axel','Rose','Clément',
  'Agathe','Noé','Elise','Léo','Juliette','Valentin','Camille','Dylan',
  'Océane','Quentin','Nora','Bastien','Lola','Samuel','Célia','Florian',
];

const LAST_NAMES = [
  'Durand','Leclerc','Moreau','Baudry','Petit','Bernard','Simon','Martin',
  'Leroy','Roux','David','Bertrand','Morel','Laurent','Lefebvre','Michel',
  'Garcia','Fournier','Girard','Andre','Mercier','Dupont','Lambert','Bonnet',
  'Francois','Martinez','Robin','Guerin','Muller','Henry','Rousseau','Nicolas',
  'Perrin','Meyer','Faure','Blanc','Gauthier','Clement','Chevalier','Mathieu',
  'Denis','Marchand','Lemaire','Picard','Renard','Collet','Breton','Masson',
  'Benoit','Brun','Dufour','Barbier','Caron','Pichon','Vidal','Aubert',
  'Maillard','Legrand','Charpentier','Royer','Tessier','Gilles','Rey','Bourgeois',
];

function generatePersons(count) {
  const persons = [];
  for (let i = 0; i < count; i++) {
    const firstName = FIRST_NAMES[i % FIRST_NAMES.length];
    const lastName = LAST_NAMES[i % LAST_NAMES.length];
    const startYear = 2019 + Math.floor(i / (count / 7));
    const colorKeys = Object.keys(PROMO_COLORS);
    const color = PROMO_COLORS[startYear] || PROMO_COLORS[colorKeys[startYear % colorKeys.length]];
    persons.push({
      id: i + 1,
      firstName,
      lastName,
      fullName: `${firstName} ${lastName}`,
      picture: i === 3 ? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&crop=face' : null,
      color,
      startYear,
      // extra mock fields
      biography: null,
      godFathers: i > 8 ? [{ id: i - 8, firstName: FIRST_NAMES[(i-8) % FIRST_NAMES.length], lastName: LAST_NAMES[(i-8) % LAST_NAMES.length] }] : [],
      godChildren: i < count - 8 ? [{ id: i + 8, firstName: FIRST_NAMES[(i+8) % FIRST_NAMES.length], lastName: LAST_NAMES[(i+8) % LAST_NAMES.length] }] : [],
    });
  }
  return persons;
}

window.MOCK_PERSONS = generatePersons(64);
window.PROMO_COLORS = PROMO_COLORS;
