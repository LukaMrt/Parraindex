/* ─── Home Page ─── */

function HomePage({ navigate }) {
  const [hovered, setHovered] = React.useState(null);
  const featured = React.useMemo(() => ALL_PERSONS.filter((_,i) => [3,12,27,8,19,35].includes(i)), []);

  return React.createElement('div', {
    style: { minHeight: 'calc(100vh - 56px)', display: 'flex', flexDirection: 'column',
      alignItems: 'center', justifyContent: 'center', padding: '60px 24px', position: 'relative', overflow: 'hidden' }
  },
    /* Floating background avatars */
    React.createElement('div', {
      style: { position: 'absolute', inset: 0, opacity: 0.06, pointerEvents: 'none', display: 'flex',
        flexWrap: 'wrap', justifyContent: 'center', alignItems: 'center', gap: 24, padding: 40 }
    }, ALL_PERSONS.slice(0, 30).map((p, i) =>
      React.createElement('div', { key: i,
        style: { width: 56, height: 56, borderRadius: 14, overflow: 'hidden' }
      }, React.createElement(Avatar, { person: p, size: 56 }))
    )),

    /* Main content */
    React.createElement('div', {
      style: { position: 'relative', zIndex: 1, textAlign: 'center', maxWidth: 600 }
    },
      React.createElement('div', {
        style: { width: 80, height: 80, borderRadius: 20, background: 'var(--dark-blue)',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          color: '#fff', fontSize: 36, fontWeight: 700, margin: '0 auto 28px',
          boxShadow: '0 8px 32px rgba(26,42,68,0.2)' }
      }, 'P'),

      React.createElement('h1', {
        style: { fontSize: 42, fontWeight: 300, color: 'var(--dark-blue)', lineHeight: 1.2, marginBottom: 8 }
      },
        React.createElement('span', { style: { fontWeight: 300, color: 'var(--medium-blue)' } }, 'Le '),
        React.createElement('span', { style: { fontWeight: 700 } }, 'Parraindex')
      ),
      React.createElement('p', {
        style: { fontSize: 16, color: 'var(--medium-blue)', lineHeight: 1.6, marginBottom: 40 }
      }, "L'annuaire des parrains et fillots de l'IUT Lyon 1. Retrouve ta famille, découvre les liens entre promotions."),

      /* CTA buttons */
      React.createElement('div', { style: { display: 'flex', gap: 12, justifyContent: 'center', flexWrap: 'wrap', marginBottom: 48 } },
        React.createElement('button', {
          onClick: () => navigate('tree'),
          style: { padding: '12px 28px', background: 'var(--dark-blue)', color: '#fff', border: 'none',
            borderRadius: 12, fontSize: 15, fontWeight: 600, cursor: 'pointer', fontFamily: 'inherit',
            boxShadow: '0 4px 16px rgba(26,42,68,0.2)', transition: 'all 0.2s' },
          onMouseEnter: e => e.currentTarget.style.transform = 'translateY(-2px)',
          onMouseLeave: e => e.currentTarget.style.transform = ''
        }, 'Découvrir ma famille'),
        React.createElement('button', {
          onClick: () => navigate('tree'),
          style: { padding: '12px 28px', background: '#fff', color: 'var(--dark-blue)',
            border: '1.5px solid #e0e0e0', borderRadius: 12, fontSize: 15, fontWeight: 600,
            cursor: 'pointer', fontFamily: 'inherit', transition: 'all 0.2s' },
          onMouseEnter: e => { e.currentTarget.style.borderColor = 'var(--dark-blue)'; e.currentTarget.style.transform = 'translateY(-2px)'; },
          onMouseLeave: e => { e.currentTarget.style.borderColor = '#e0e0e0'; e.currentTarget.style.transform = ''; }
        }, 'Parcourir le répertoire')
      ),

      /* Featured people preview */
      React.createElement('div', { style: { display: 'flex', justifyContent: 'center', gap: 8 } },
        featured.map((p, i) =>
          React.createElement('div', {
            key: p.id,
            onClick: () => navigate('person', p.id),
            onMouseEnter: () => setHovered(p.id),
            onMouseLeave: () => setHovered(null),
            style: { width: hovered === p.id ? 56 : 44, height: hovered === p.id ? 56 : 44,
              borderRadius: 14, overflow: 'hidden', cursor: 'pointer',
              transition: 'all 0.3s ease', border: '2px solid #fff',
              boxShadow: hovered === p.id ? `0 8px 24px ${p.color}40` : '0 2px 8px rgba(0,0,0,0.1)',
              transform: hovered === p.id ? 'translateY(-4px)' : '' }
          }, React.createElement(Avatar, { person: p, size: 56 }))
        )
      ),
      React.createElement('p', {
        style: { fontSize: 13, color: 'var(--medium-grey)', marginTop: 12 }
      }, `${ALL_PERSONS.length} personnes inscrites`)
    ),

    /* Stats row */
    React.createElement('div', {
      style: { display: 'flex', gap: 32, marginTop: 56, position: 'relative', zIndex: 1 }
    },
      [
        { n: ALL_PERSONS.length, label: 'Personnes' },
        { n: YEARS.length, label: 'Promotions' },
        { n: ALL_PERSONS.reduce((s,p) => s + p.godFathers.length, 0), label: 'Parrainages' },
      ].map((s, i) =>
        React.createElement('div', { key: i, style: { textAlign: 'center' } },
          React.createElement('div', { style: { fontSize: 28, fontWeight: 700, color: 'var(--dark-blue)' } }, s.n),
          React.createElement('div', { style: { fontSize: 13, color: 'var(--medium-grey)', fontWeight: 500 } }, s.label)
        )
      )
    )
  );
}

window.HomePage = HomePage;
