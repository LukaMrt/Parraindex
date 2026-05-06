/* Shared UI primitives — Header, Avatar, links between pages */
const { useState: useStateUI } = React;

function Avatar({ person, size = 80, square = false }) {
  const [imgErr, setImgErr] = useStateUI(false);
  const radius = square ? '14%' : '50%';
  // size > 200 means "fill parent" — used by GridView where the parent has fixed aspect ratio
  const fill = size > 200;
  const dims = fill
    ? { width: '100%', height: '100%' }
    : { width: size, height: size };
  if (person.picture && !imgErr) {
    return React.createElement('img', {
      src: person.picture, alt: person.fullName,
      style: { ...dims, objectFit: 'cover', borderRadius: fill ? 0 : radius, display: 'block' },
      onError: () => setImgErr(true), loading: 'lazy'
    });
  }
  const initials = (person.firstName[0] + person.lastName[0]).toUpperCase();
  const fontSize = fill ? 32 : size * 0.36;
  return React.createElement('div', {
    style: {
      ...dims, background: person.color + '1F', color: person.color,
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      fontWeight: 600, fontSize, letterSpacing: '0.02em',
      borderRadius: fill ? 0 : radius,
    }
  }, initials);
}

function Header({ currentPage, navigate }) {
  const link = (page, label) => React.createElement('a', {
    href: '#', onClick: e => { e.preventDefault(); navigate(page); },
    style: {
      color: currentPage === page ? 'var(--ink)' : 'var(--ink-3)',
      textDecoration: 'none', fontWeight: currentPage === page ? 600 : 500,
      fontSize: 14, padding: '8px 4px', borderBottom: currentPage === page ? '2px solid var(--ink)' : '2px solid transparent',
      transition: 'color 0.15s',
    }
  }, label);
  return React.createElement('header', {
    style: {
      display:'flex',alignItems:'center',justifyContent:'space-between',
      padding:'0 28px',background:'#fff',borderBottom:'1px solid var(--line)',
      position:'sticky',top:0,zIndex:50,height:60,
    }
  },
    React.createElement('div', { style: { display:'flex',alignItems:'center',gap:32 } },
      React.createElement('a', {
        href: '#', onClick: e => { e.preventDefault(); navigate('home'); },
        style: { display:'flex',alignItems:'center',gap:10,textDecoration:'none' }
      },
        React.createElement('div', {
          style: {
            width:30,height:30,borderRadius:8,background:'var(--ink)',
            display:'flex',alignItems:'center',justifyContent:'center',color:'#fff',
            fontSize:13,fontWeight:700,letterSpacing:'-0.02em',
          }
        }, 'P'),
        React.createElement('span', { style: { fontWeight:600,fontSize:15.5,color:'var(--ink)',letterSpacing:'-0.01em' } }, 'Parraindex'),
      ),
      React.createElement('nav', { style: { display:'flex',gap:24,alignItems:'center' } },
        link('directory','Annuaire'),
        link('link','Lien entre 2'),
        link('about','À propos'),
      ),
    ),
    React.createElement('div', { style: { display:'flex',gap:8,alignItems:'center' } },
      React.createElement('a', {
        href:'#', style: { color:'var(--ink-3)',textDecoration:'none',fontSize:13.5,fontWeight:500,padding:'8px 12px' }
      }, 'Se connecter'),
      React.createElement('a', {
        href:'#', style: {
          background:'var(--ink)',color:'#fff',textDecoration:'none',fontSize:13.5,fontWeight:500,
          padding:'8px 14px',borderRadius:8,
        }
      }, "S'inscrire"),
    )
  );
}

window.UI = { Avatar, Header };
