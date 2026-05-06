/* ─── Shared components used across all pages ─── */

const PROMO_COLORS = {
  2019: '#2E4057', 2020: '#48BFA0', 2021: '#E85D75', 2022: '#F4A236',
  2023: '#6C63FF', 2024: '#2196F3', 2025: '#8BC34A',
};
const FIRST_NAMES = ['Henri','Camille','Baptiste','Lilian','Marine','Thomas','Pauline','Maxime','Léa','Antoine','Clara','Julien','Emma','Lucas','Manon','Hugo','Chloé','Nathan','Sarah','Théo','Alice','Romain','Inès','Alexandre','Louise','Adrien','Margaux','Enzo','Jade','Raphaël','Zoé','Gabriel','Lina','Arthur','Eva','Louis','Ambre','Paul','Anaïs','Victor','Lucie','Ethan','Mathilde','Tom','Charlotte','Axel','Rose','Clément','Agathe','Noé','Elise','Léo','Juliette','Valentin','Sofia','Dylan','Océane','Quentin','Nora','Bastien','Lola','Samuel','Célia','Florian'];
const LAST_NAMES = ['Durand','Leclerc','Moreau','Baudry','Petit','Bernard','Simon','Martin','Leroy','Roux','David','Bertrand','Morel','Laurent','Lefebvre','Michel','Garcia','Fournier','Girard','Andre','Mercier','Dupont','Lambert','Bonnet','Francois','Martinez','Robin','Guerin','Muller','Henry','Rousseau','Nicolas','Perrin','Meyer','Faure','Blanc','Gauthier','Clement','Chevalier','Mathieu','Denis','Marchand','Lemaire','Picard','Renard','Collet','Breton','Masson','Benoit','Brun','Dufour','Barbier','Caron','Pichon','Vidal','Aubert','Maillard','Legrand','Charpentier','Royer','Tessier','Gilles','Rey','Bourgeois'];
const BIOS = [
  "Passionné d'informatique depuis toujours, je code le soir et le weekend.",
  "Fan de randonnée et de jeux vidéo. Toujours partant pour un bon repas.",
  "Développeur web le jour, musicien la nuit. J'adore les défis techniques.",
  null,
  "Curieux de nature, j'aime découvrir de nouvelles technologies.",
  "Sportif dans l'âme, je pratique le basket et la natation.",
  null,
  "Grande lectrice, j'adore les romans de science-fiction.",
];
const CHARACTERISTICS = [
  { typeTitle: 'Discord', value: 'user#1234', typeUrl: null, visible: true },
  { typeTitle: 'GitHub', value: 'github_user', typeUrl: 'https://github.com/', visible: true },
  { typeTitle: 'LinkedIn', value: 'linkedin_user', typeUrl: 'https://linkedin.com/in/', visible: true },
];

function generatePersons(count) {
  const p = [];
  for (let i = 0; i < count; i++) {
    const startYear = 2019 + Math.floor(i / (count / 7));
    const color = PROMO_COLORS[startYear] || '#999';
    const fn = FIRST_NAMES[i % FIRST_NAMES.length];
    const ln = LAST_NAMES[i % LAST_NAMES.length];
    p.push({
      id: i + 1, firstName: fn, lastName: ln, fullName: `${fn} ${ln}`,
      picture: (i===3||i===12||i===27||i===41) ? `https://i.pravatar.cc/200?img=${10+i}` : null,
      color, startYear,
      biography: BIOS[i % BIOS.length],
      description: i % 3 === 0 ? `Étudiant en informatique à l'IUT Lyon 1, promotion ${startYear}/${startYear+1}.` : null,
      characteristics: i % 4 === 0 ? CHARACTERISTICS.map((c,ci) => ({...c, id: i*10+ci})) : [],
      godFathers: [],
      godChildren: [],
    });
  }
  // Build sponsorship links
  for (let i = 0; i < count; i++) {
    const godFatherIdx = i - Math.floor(count / 7);
    if (godFatherIdx >= 0) {
      p[i].godFathers.push({
        id: 1000 + i, godFatherId: p[godFatherIdx].id, godFatherName: p[godFatherIdx].fullName,
        godChildId: p[i].id, godChildName: p[i].fullName,
        type: i % 5 === 0 ? 'HEART' : 'CLASSIC', date: `${p[godFatherIdx].startYear + 1}-09-15`,
        description: i % 3 === 0 ? 'Un super parrainage, plein de bons moments passés ensemble !' : null,
        godFatherColor: p[godFatherIdx].color, godChildColor: p[i].color,
        godFatherPicture: p[godFatherIdx].picture, godChildPicture: p[i].picture,
      });
      p[godFatherIdx].godChildren.push(p[i].godFathers[0]);
    }
  }
  return p;
}

const ALL_PERSONS = generatePersons(64);
const YEARS = [...new Set(ALL_PERSONS.map(p => p.startYear))].sort();

function getPersonById(id) { return ALL_PERSONS.find(p => p.id === id) || null; }
function getSponsorById(id) {
  for (const p of ALL_PERSONS) {
    for (const gf of p.godFathers) { if (gf.id === id) return gf; }
  }
  return null;
}

/* ─── Avatar ─── */
function Avatar({ person, size = 80, style: extraStyle }) {
  const [imgErr, setImgErr] = React.useState(false);
  const baseStyle = { width: size, height: size, borderRadius: 'inherit', display: 'block', flexShrink: 0, ...extraStyle };
  if (person.picture && !imgErr) {
    return React.createElement('img', { src: person.picture, alt: person.fullName,
      style: { ...baseStyle, objectFit: 'cover' }, onError: () => setImgErr(true), loading: 'lazy' });
  }
  const initials = ((person.firstName || 'X')[0] + (person.lastName || 'X')[0]).toUpperCase();
  return React.createElement('div', {
    style: { ...baseStyle, background: person.color + '18', color: person.color,
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      fontWeight: 600, fontSize: size * 0.36, letterSpacing: '0.02em' }
  }, initials);
}

/* ─── Header ─── */
function Header({ navigate }) {
  return React.createElement('header', {
    style: { display: 'flex', alignItems: 'center', justifyContent: 'space-between',
      padding: '12px 24px', background: '#fff', boxShadow: '0 1px 3px rgba(0,0,0,0.06)',
      position: 'sticky', top: 0, zIndex: 50 }
  },
    React.createElement('div', {
      style: { display: 'flex', alignItems: 'center', gap: 10, cursor: 'pointer' },
      onClick: () => navigate('home')
    },
      React.createElement('div', {
        style: { width: 32, height: 32, borderRadius: 8, background: 'var(--dark-blue)',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          color: '#fff', fontSize: 16, fontWeight: 700 }
      }, 'P'),
      React.createElement('span', { style: { fontWeight: 700, fontSize: 17, color: 'var(--dark-blue)' } }, 'Parraindex')
    ),
    React.createElement('nav', { style: { display: 'flex', gap: 16, fontSize: 14 } },
      React.createElement('a', { href: '#', style: { color: 'var(--dark-blue)', textDecoration: 'none', fontWeight: 500 } }, 'Se connecter'),
      React.createElement('a', { href: '#', style: { color: 'var(--dark-blue)', textDecoration: 'none', fontWeight: 500 } }, "S'inscrire")
    )
  );
}

window.PROMO_COLORS = PROMO_COLORS;
window.ALL_PERSONS = ALL_PERSONS;
window.YEARS = YEARS;
window.getPersonById = getPersonById;
window.getSponsorById = getSponsorById;
window.Avatar = Avatar;
window.Header = Header;
