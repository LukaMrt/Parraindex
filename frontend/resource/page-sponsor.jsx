/* ─── Sponsor Link Page ─── */

const SPONSOR_TYPE_LABELS = { HEART: 'Parrainage de cœur', CLASSIC: 'Parrainage classique', UNKNOWN: 'Lien inconnu' };

function SponsorPage({ sponsorId, navigate }) {
  const sponsor = getSponsorById(sponsorId);
  if (!sponsor) return React.createElement('div', { style: { padding: 40, textAlign: 'center', color: 'var(--medium-grey)' } }, 'Parrainage introuvable.');

  const godFather = getPersonById(sponsor.godFatherId);
  const godChild = getPersonById(sponsor.godChildId);
  const isHeart = sponsor.type === 'HEART';

  function PersonBubble({ person, label }) {
    if (!person) return null;
    return React.createElement('div', {
      onClick: () => navigate('person', person.id),
      style: { display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 10, cursor: 'pointer', transition: 'transform 0.2s' },
      onMouseEnter: e => e.currentTarget.style.transform = 'translateY(-4px)',
      onMouseLeave: e => e.currentTarget.style.transform = ''
    },
      React.createElement('div', {
        style: { width: 96, height: 96, borderRadius: 24, overflow: 'hidden',
          boxShadow: `0 8px 28px ${person.color}30`, border: `2px solid ${person.color}20` }
      }, React.createElement(Avatar, { person, size: 96 })),
      React.createElement('span', { style: { fontSize: 15, fontWeight: 700, color: 'var(--dark-blue)' } }, person.fullName),
      React.createElement('span', {
        style: { fontSize: 12, padding: '3px 10px', borderRadius: 16, background: person.color + '15', color: person.color, fontWeight: 600 }
      }, `Promo ${person.startYear}/${person.startYear+1}`),
      React.createElement('span', { style: { fontSize: 12, color: 'var(--medium-grey)', fontWeight: 500 } }, label)
    );
  }

  return React.createElement('div', {
    style: { maxWidth: 700, margin: '0 auto', padding: '32px 24px 80px' }
  },
    React.createElement('button', {
      onClick: () => window.history.length > 1 ? navigate('tree') : navigate('tree'),
      style: { display: 'flex', alignItems: 'center', gap: 6, background: 'none', border: 'none',
        color: 'var(--medium-blue)', fontSize: 14, cursor: 'pointer', fontFamily: 'inherit', marginBottom: 32, padding: 0 }
    }, '← Retour'),

    /* Title */
    React.createElement('div', { style: { textAlign: 'center', marginBottom: 40 } },
      React.createElement('div', {
        style: { display: 'inline-flex', alignItems: 'center', gap: 8, padding: '8px 20px',
          borderRadius: 24, background: isHeart ? '#FFF0F0' : '#F0F4FF', marginBottom: 12 }
      },
        React.createElement('span', { style: { fontSize: 18 } }, isHeart ? '♥' : '★'),
        React.createElement('span', { style: { fontSize: 15, fontWeight: 700, color: isHeart ? '#E85D75' : 'var(--dark-blue)' } },
          SPONSOR_TYPE_LABELS[sponsor.type])
      ),
      sponsor.date && React.createElement('p', {
        style: { fontSize: 14, color: 'var(--medium-grey)' }
      }, new Date(sponsor.date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }))
    ),

    /* Two persons with link */
    React.createElement('div', {
      style: { display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 40, flexWrap: 'wrap', marginBottom: 40 }
    },
      React.createElement(PersonBubble, { person: godFather, label: 'Parrain' }),

      /* Connection line */
      React.createElement('div', { style: { display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 4 } },
        React.createElement('div', {
          style: { width: 60, height: 2, background: isHeart
            ? 'linear-gradient(90deg, #E85D75, #F4A236)' : 'linear-gradient(90deg, var(--light-blue), var(--medium-blue))',
            borderRadius: 1 }
        }),
        React.createElement('span', { style: { fontSize: 18 } }, isHeart ? '♥' : '→')
      ),

      React.createElement(PersonBubble, { person: godChild, label: 'Fillot' })
    ),

    /* Description */
    sponsor.description && React.createElement('div', {
      style: { textAlign: 'center', padding: '20px 24px', background: '#fff', borderRadius: 16,
        border: '1px solid #f0f0f0', maxWidth: 500, margin: '0 auto' }
    },
      React.createElement('p', { style: { fontSize: 14, lineHeight: 1.7, color: 'var(--dark-blue)', fontStyle: 'italic' } },
        `"${sponsor.description}"`)
    )
  );
}

window.SponsorPage = SponsorPage;
