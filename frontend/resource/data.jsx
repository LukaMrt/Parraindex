// Shared mock data + utilities for all Parraindex pages
const PROMO_COLORS = {
  2019: '#2E4057', 2020: '#48BFA0', 2021: '#E85D75', 2022: '#F4A236',
  2023: '#6C63FF', 2024: '#2196F3', 2025: '#8BC34A',
};
const FIRST_NAMES = ['Henri','Camille','Baptiste','Lilian','Marine','Thomas','Pauline','Maxime','Léa','Antoine','Clara','Julien','Emma','Lucas','Manon','Hugo','Chloé','Nathan','Sarah','Théo','Alice','Romain','Inès','Alexandre','Louise','Adrien','Margaux','Enzo','Jade','Raphaël','Zoé','Gabriel','Lina','Arthur','Eva','Louis','Ambre','Paul','Anaïs','Victor','Lucie','Ethan','Mathilde','Tom','Charlotte','Axel','Rose','Clément','Agathe','Noé','Elise','Léo','Juliette','Valentin','Sofia','Dylan','Océane','Quentin','Nora','Bastien','Lola','Samuel','Célia','Florian'];
const LAST_NAMES = ['Durand','Leclerc','Moreau','Baudry','Petit','Bernard','Simon','Martin','Leroy','Roux','David','Bertrand','Morel','Laurent','Lefebvre','Michel','Garcia','Fournier','Girard','Andre','Mercier','Dupont','Lambert','Bonnet','Francois','Martinez','Robin','Guerin','Muller','Henry','Rousseau','Nicolas','Perrin','Meyer','Faure','Blanc','Gauthier','Clement','Chevalier','Mathieu','Denis','Marchand','Lemaire','Picard','Renard','Collet','Breton','Masson','Benoit','Brun','Dufour','Barbier','Caron','Pichon','Vidal','Aubert','Maillard','Legrand','Charpentier','Royer','Tessier','Gilles','Rey','Bourgeois'];
const BIOS = [
  "Passionné·e de dev web, café et trail. Toujours partant·e pour un débat sur les frameworks JS.",
  "BUT Info, spécialité réseaux. Joue de la guitare basse dans un groupe le week-end.",
  "Adore le pixel art et les jeux indé. Cherche toujours la promo qui a le meilleur dress code.",
  "Stage chez une scale-up parisienne. Rentre à Lyon pour les bons restos et les soirées BDE.",
  "Triple casquette : étudiant·e, alternant·e, et organisateur·trice de soirées techno.",
  "Photographe argentique amateur. Partagera volontiers une bière en terrasse.",
  null, null,
];

function generatePersons(count) {
  const persons = [];
  for (let i = 0; i < count; i++) {
    const startYear = 2019 + Math.floor(i / (count / 7));
    const color = PROMO_COLORS[startYear] || '#999';
    persons.push({
      id: i + 1,
      firstName: FIRST_NAMES[i % FIRST_NAMES.length],
      lastName: LAST_NAMES[i % LAST_NAMES.length],
      fullName: `${FIRST_NAMES[i%FIRST_NAMES.length]} ${LAST_NAMES[i%LAST_NAMES.length]}`,
      picture: (i===3||i===12||i===27||i===41||i===8||i===19) ? `https://i.pravatar.cc/200?img=${10+i}` : null,
      color,
      startYear,
      biography: BIOS[i % BIOS.length],
      city: ['Lyon','Villeurbanne','Caluire','Bron','Vaulx-en-Velin'][i % 5],
      tags: [
        ['Dev Web','React','Symfony'],['Réseau','Sécurité'],['Data','Python'],['Mobile','Flutter'],
        ['Design','UX'],['Cloud','DevOps'],['Jeu vidéo'],['Photo','Musique'],
      ][i % 8],
    });
  }
  return persons;
}

const ALL_PERSONS = generatePersons(64);
const YEARS = [...new Set(ALL_PERSONS.map(p=>p.startYear))].sort();

// Generate sponsorship links — each person 0-3 godchildren in following years
const LINKS = []; // {godFatherId, godChildId}
ALL_PERSONS.forEach(p => {
  const candidates = ALL_PERSONS.filter(c => c.startYear === p.startYear + 1);
  if (candidates.length === 0) return;
  const numChildren = (p.id * 7) % 4; // 0..3, deterministic
  const used = new Set();
  for (let k = 0; k < numChildren; k++) {
    const idx = (p.id * (k+3) * 11) % candidates.length;
    const c = candidates[idx];
    if (!c || used.has(c.id)) continue;
    // Check child doesn't already have 2 godparents
    const childParents = LINKS.filter(l => l.godChildId === c.id).length;
    if (childParents >= 2) continue;
    used.add(c.id);
    // Enrich link with metadata: date, type, reason
    const types = ['iut','coeur','faluche','autre'];
    const reasons = [
      "Rencontre lors de la soirée d'intégration. On a tout de suite accroché autour d'un débat sur Vim vs VSCode.",
      "Voisins de TP toute l'année — il/elle m'a sauvé sur le projet S3.",
      "Coup de cœur au week-end d'intégration. La famille était écrite.",
      "On s'est croisés au BDE et on ne s'est plus quittés depuis.",
      "Présenté·e par un parrain commun, le courant est passé direct.",
      "Soirée Faluche mémorable, baptisé·e dans la foulée.",
      "Stage commun, on s'est entraidés sur les rapports.",
      null,
    ];
    const seed = (p.id * 31 + c.id * 17) % 1000;
    const month = (seed % 9) + 1; // sept-may
    const day = (seed % 27) + 1;
    const yearVal = month >= 9 ? c.startYear : c.startYear + 1;
    LINKS.push({
      godFatherId: p.id,
      godChildId: c.id,
      type: types[seed % types.length],
      date: `${yearVal}-${String(month).padStart(2,'0')}-${String(day).padStart(2,'0')}`,
      reason: reasons[seed % reasons.length],
      validated: seed % 5 !== 0,
    });
  }
});

function getGodFathers(personId) {
  return LINKS.filter(l => l.godChildId === personId).map(l => ALL_PERSONS.find(p => p.id === l.godFatherId)).filter(Boolean);
}
function getGodChildren(personId) {
  return LINKS.filter(l => l.godFatherId === personId).map(l => ALL_PERSONS.find(p => p.id === l.godChildId)).filter(Boolean);
}

// BFS path between two persons through sponsorship links (undirected)
function findPath(fromId, toId) {
  if (fromId === toId) return [fromId];
  const visited = new Set([fromId]);
  const queue = [[fromId]];
  while (queue.length > 0) {
    const path = queue.shift();
    const last = path[path.length - 1];
    const neighbors = [
      ...LINKS.filter(l => l.godFatherId === last).map(l => l.godChildId),
      ...LINKS.filter(l => l.godChildId === last).map(l => l.godFatherId),
    ];
    for (const n of neighbors) {
      if (visited.has(n)) continue;
      const newPath = [...path, n];
      if (n === toId) return newPath;
      visited.add(n);
      queue.push(newPath);
    }
  }
  return null;
}

function findLink(aId, bId) {
  return LINKS.find(l =>
    (l.godFatherId === aId && l.godChildId === bId) ||
    (l.godFatherId === bId && l.godChildId === aId)
  );
}

window.PARRAINDEX = { ALL_PERSONS, YEARS, PROMO_COLORS, LINKS, getGodFathers, getGodChildren, findPath, findLink };
