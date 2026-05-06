/* ─── Person Profile Page ─── */

const TYPE_ICONS = { HEART: '♥', CLASSIC: '★', UNKNOWN: '?' };
const TYPE_LABELS = { HEART: 'Parrainage de cœur', CLASSIC: 'Parrainage classique', UNKNOWN: 'Lien inconnu' };

function PersonProfilePage({ personId, navigate }) {
  const person = getPersonById(personId);
  if (!person) return React.createElement('div', { style: { padding: 40, textAlign: 'center', color: 'var(--medium-grey)' } }, 'Personne introuvable.');

  const endYear = person.startYear + (person.startYear < 2021 ? 2 : 3);

  return React.createElement('div', {
    style: { maxWidth: 900, margin: '0 auto', padding: '32px 24px 80px' }
  },
    /* Back */
    React.createElement('button', {
      onClick: () => navigate('tree'),
      style: { display: 'flex', alignItems: 'center', gap: 6, background: 'none', border: 'none',
        color: 'var(--medium-blue)', fontSize: 14, cursor: 'pointer', fontFamily: 'inherit',
        marginBottom: 24, padding: 0 },
      onMouseEnter: e => e.currentTarget.style.color = 'var(--dark-blue)',
      onMouseLeave: e => e.currentTarget.style.color = 'var(--medium-blue)'
    }, '← Retour au répertoire'),

    /* Top section: avatar + info */
    React.createElement('div', {
      style: { display: 'flex', gap: 32, alignItems: 'flex-start', marginBottom: 40, flexWrap: 'wrap' }
    },
      /* Avatar + family tree */
      React.createElement('div', {
        style: { display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 16, minWidth: 160 }
      },
        /* Godparents above */
        person.godFathers.length > 0 && React.createElement('div', {
          style: { display: 'flex', gap: 8, marginBottom: 4 }
        }, person.godFathers.map(gf =>
          React.createElement('div', {
            key: gf.id, onClick: () => navigate('person', gf.godFatherId),
            style: { display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 4, cursor: 'pointer' }
          },
            React.createElement('div', { style: { width: 40, height: 40, borderRadius: 12, overflow: 'hidden' } },
              React.createElement(Avatar, { person: { ...getPersonById(gf.godFatherId), firstName: gf.godFatherName.split(' ')[0], lastName: gf.godFatherName.split(' ')[1] || '' }, size: 40 })
            ),
            React.createElement('span', { style: { fontSize: 10, color: 'var(--medium-blue)', textAlign: 'center', maxWidth: 70 } }, gf.godFatherName),
            React.createElement('span', { style: { fontSize: 9, color: 'var(--medium-grey)' } }, 'Parrain')
          )
        )),

        person.godFathers.length > 0 && React.createElement('div', {
          style: { width: 1, height: 16, background: 'var(--medium-grey)', opacity: 0.3 }
        }),

        /* Main avatar */
        React.createElement('div', {
          style: { width: 120, height: 120, borderRadius: 24, overflow: 'hidden',
            boxShadow: `0 12px 40px ${person.color}30`, border: `3px solid ${person.color}25` }
        }, React.createElement(Avatar, { person, size: 120 })),

        person.godChildren.length > 0 && React.createElement('div', {
          style: { width: 1, height: 16, background: 'var(--medium-grey)', opacity: 0.3 }
        }),

        /* Godchildren below */
        person.godChildren.length > 0 && React.createElement('div', {
          style: { display: 'flex', gap: 8, flexWrap: 'wrap', justifyContent: 'center' }
        }, person.godChildren.map(gc =>
          React.createElement('div', {
            key: gc.id, onClick: () => navigate('person', gc.godChildId),
            style: { display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 4, cursor: 'pointer' }
          },
            React.createElement('div', { style: { width: 40, height: 40, borderRadius: 12, overflow: 'hidden' } },
              React.createElement(Avatar, { person: { ...getPersonById(gc.godChildId), firstName: gc.godChildName.split(' ')[0], lastName: gc.godChildName.split(' ')[1] || '' }, size: 40 })
            ),
            React.createElement('span', { style: { fontSize: 10, color: 'var(--medium-blue)', textAlign: 'center', maxWidth: 70 } }, gc.godChildName),
            React.createElement('span', { style: { fontSize: 9, color: 'var(--medium-grey)' } }, 'Fillot')
          )
        ))
      ),

      /* Info */
      React.createElement('div', { style: { flex: 1, minWidth: 240 } },
        React.createElement('h1', { style: { fontSize: 28, fontWeight: 700, color: 'var(--dark-blue)', marginBottom: 4 } },
          person.firstName, ' ',
          React.createElement('span', { style: { color: 'var(--medium-blue)' } }, person.lastName.toUpperCase())
        ),
        React.createElement('div', {
          style: { display: 'inline-flex', alignItems: 'center', gap: 6, padding: '4px 12px',
            borderRadius: 20, background: person.color + '15', marginBottom: 20 }
        },
          React.createElement('div', { style: { width: 8, height: 8, borderRadius: '50%', background: person.color } }),
          React.createElement('span', { style: { fontSize: 13, fontWeight: 600, color: person.color } },
            `Promo ${person.startYear} / ${endYear}`)
        ),

        /* Biography */
        person.description && React.createElement('div', { style: { marginBottom: 20 } },
          React.createElement('h2', { style: { fontSize: 12, fontWeight: 700, textTransform: 'uppercase',
            letterSpacing: '0.06em', color: 'var(--medium-grey)', marginBottom: 8 } }, 'À propos'),
          React.createElement('p', { style: { fontSize: 14, lineHeight: 1.7, color: 'var(--dark-blue)' } }, person.description)
        ),
        person.biography && React.createElement('p', {
          style: { fontSize: 14, lineHeight: 1.7, color: 'var(--medium-blue)', marginBottom: 20, fontStyle: 'italic' }
        }, person.biography),

        /* Characteristics */
        person.characteristics.length > 0 && React.createElement('div', { style: { marginBottom: 20 } },
          React.createElement('h2', { style: { fontSize: 12, fontWeight: 700, textTransform: 'uppercase',
            letterSpacing: '0.06em', color: 'var(--medium-grey)', marginBottom: 8 } }, 'Liens'),
          React.createElement('div', { style: { display: 'flex', flexWrap: 'wrap', gap: 8 } },
            person.characteristics.filter(c => c.visible && c.value).map(c =>
              React.createElement('a', {
                key: c.id, href: c.typeUrl ? c.typeUrl + c.value : '#',
                target: '_blank', rel: 'noopener',
                style: { display: 'flex', alignItems: 'center', gap: 6, padding: '6px 14px',
                  borderRadius: 10, background: '#fff', border: '1px solid #e8e8e8', fontSize: 13,
                  color: 'var(--dark-blue)', textDecoration: 'none', fontWeight: 500, transition: 'all 0.15s' },
                onMouseEnter: e => e.currentTarget.style.borderColor = 'var(--light-blue)',
                onMouseLeave: e => e.currentTarget.style.borderColor = '#e8e8e8'
              }, React.createElement('span', { style: { color: 'var(--medium-grey)' } }, c.typeTitle),
                React.createElement('span', null, c.value))
            )
          )
        ),

        /* Sponsorship links */
        (person.godFathers.length > 0 || person.godChildren.length > 0) &&
        React.createElement('div', null,
          React.createElement('h2', { style: { fontSize: 12, fontWeight: 700, textTransform: 'uppercase',
            letterSpacing: '0.06em', color: 'var(--medium-grey)', marginBottom: 10 } }, 'Parrainages'),
          React.createElement('div', { style: { display: 'flex', flexDirection: 'column', gap: 6 } },
            [...person.godFathers, ...person.godChildren].map(s =>
              React.createElement('div', {
                key: s.id, onClick: () => navigate('sponsor', s.id),
                style: { display: 'flex', alignItems: 'center', gap: 12, padding: '10px 14px',
                  background: '#fff', borderRadius: 12, cursor: 'pointer', border: '1px solid #f0f0f0',
                  transition: 'all 0.15s' },
                onMouseEnter: e => { e.currentTarget.style.background = '#f8f9ff'; e.currentTarget.style.borderColor = '#e0e4f0'; },
                onMouseLeave: e => { e.currentTarget.style.background = '#fff'; e.currentTarget.style.borderColor = '#f0f0f0'; }
              },
                React.createElement('span', { style: { fontSize: 14 } }, TYPE_ICONS[s.type] || '?'),
                React.createElement('span', { style: { fontSize: 13, fontWeight: 600, color: 'var(--dark-blue)' } },
                  s.godFatherId === person.id ? s.godChildName : s.godFatherName),
                React.createElement('span', { style: { fontSize: 11, color: 'var(--medium-grey)', marginLeft: 'auto' } },
                  s.godFatherId === person.id ? 'Fillot' : 'Parrain'),
                React.createElement('span', { style: { fontSize: 11, color: 'var(--medium-grey)' } }, TYPE_LABELS[s.type])
              )
            )
          )
        )
      )
    )
  );
}

window.PersonProfilePage = PersonProfilePage;
