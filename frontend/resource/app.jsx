const { useState, useMemo, useRef, useEffect, useCallback } = React;
const { ALL_PERSONS, YEARS, PROMO_COLORS, LINKS, getGodFathers, getGodChildren, findPath, findLink } = window.PARRAINDEX;
const { Avatar, Header } = window.UI;

const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{"directoryView":"grid","cardStyle":"modern","showPromoBar":true,"treeFilterMode":"all","treeShowLabels":true,"theme":"light"}/*EDITMODE-END*/;

/* ─── Person detail modal — inline (used by directory) ─── */
function PersonModal({ person, onClose, onOpenProfile }) {
  if (!person) return null;
  useEffect(() => {
    const h = e => e.key === 'Escape' && onClose();
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [onClose]);
  const fathers = getGodFathers(person.id);
  const children = getGodChildren(person.id);

  return React.createElement('div', {
    style: { position:'fixed',inset:0,zIndex:100,display:'flex',alignItems:'center',justifyContent:'center',
      background:'rgba(20,22,28,0.45)',backdropFilter:'blur(8px)',animation:'fadeIn 0.2s ease' },
    onClick: e => e.target === e.currentTarget && onClose()
  },
    React.createElement('div', {
      style: { background:'var(--surface)',borderRadius:16,padding:28,maxWidth:420,width:'90%',
        boxShadow:'0 24px 64px rgba(0,0,0,0.18)',animation:'modalIn 0.25s ease',border:'1px solid var(--line)' }
    },
      React.createElement('div', { style: { display:'flex',alignItems:'center',gap:18,marginBottom:18 } },
        React.createElement('div', { style: { flexShrink:0 } },
          React.createElement(Avatar, { person, size: 64, square: true })
        ),
        React.createElement('div', null,
          React.createElement('div', { style: { fontSize:18,fontWeight:600,color:'var(--ink)',letterSpacing:'-0.01em' } },
            person.firstName + ' ' + person.lastName),
          React.createElement('div', { style: { fontSize:13,color:'var(--ink-3)',marginTop:3 } },
            `Promo ${person.startYear} / ${person.startYear+1} · ${person.city}`),
        )
      ),
      React.createElement('div', { style: { display:'flex',gap:24,fontSize:13,marginBottom:18 } },
        React.createElement('div', null,
          React.createElement('div', { style: { color:'var(--ink-3)',fontSize:11,textTransform:'uppercase',letterSpacing:'0.06em',marginBottom:4 } }, 'Parrains'),
          React.createElement('div', { style: { fontSize:18,fontWeight:600 } }, fathers.length),
        ),
        React.createElement('div', null,
          React.createElement('div', { style: { color:'var(--ink-3)',fontSize:11,textTransform:'uppercase',letterSpacing:'0.06em',marginBottom:4 } }, 'Fillots'),
          React.createElement('div', { style: { fontSize:18,fontWeight:600 } }, children.length),
        ),
      ),
      React.createElement('div', { style: { display:'flex',gap:10 } },
        React.createElement('button', {
          onClick: () => onOpenProfile(person.id),
          style: { flex:1,padding:'10px 0',border:'none',borderRadius:9,background:'var(--ink)',color:'#fff',
            fontSize:13.5,fontWeight:500,cursor:'pointer',fontFamily:'inherit' }
        }, 'Voir le profil'),
        React.createElement('button', {
          onClick: onClose,
          style: { padding:'10px 16px',border:'1px solid var(--line)',borderRadius:9,background:'transparent',color:'var(--ink-2)',
            fontSize:13.5,fontWeight:500,cursor:'pointer',fontFamily:'inherit' }
        }, 'Fermer')
      )
    )
  );
}

/* ============================================================
 * HOME PAGE
 * ============================================================ */
function HomePage({ navigate }) {
  return React.createElement('div', {
    style: { minHeight:'calc(100vh - 60px)',display:'flex',alignItems:'center',justifyContent:'center',padding:'40px 24px',
      background:'var(--bg)' }
  },
    React.createElement('div', { style: { maxWidth:1100,width:'100%' } },
      /* Hero */
      React.createElement('div', {
        style: { textAlign:'center',marginBottom:64 }
      },
        React.createElement('div', {
          style: { display:'inline-flex',alignItems:'center',gap:8,fontSize:12.5,color:'var(--ink-3)',
            background:'var(--surface)',border:'1px solid var(--line)',padding:'5px 12px',borderRadius:20,marginBottom:24 }
        },
          React.createElement('span', { style: { width:6,height:6,borderRadius:'50%',background:'#48BFA0' } }),
          `${ALL_PERSONS.length} étudiants · ${LINKS.length} parrainages · ${YEARS.length} promotions`
        ),
        React.createElement('h1', {
          style: { fontSize:'clamp(40px,6vw,68px)',fontWeight:600,letterSpacing:'-0.03em',lineHeight:1.05,
            color:'var(--ink)',marginBottom:20 }
        },
          "L'annuaire des parrains",
          React.createElement('br', null),
          React.createElement('span', { style: { color:'var(--ink-3)' } }, "de l'IUT Lyon 1"),
        ),
        React.createElement('p', {
          style: { fontSize:17,color:'var(--ink-2)',maxWidth:540,margin:'0 auto 32px',lineHeight:1.5 }
        }, "Visualisez les liens de parrainage entre étudiants, retrouvez votre famille, et explorez les promotions au fil des années."),
        React.createElement('div', { style: { display:'flex',gap:12,justifyContent:'center',flexWrap:'wrap' } },
          React.createElement('button', {
            onClick: () => navigate('directory'),
            style: { padding:'13px 22px',border:'none',borderRadius:10,background:'var(--ink)',color:'#fff',
              fontSize:14.5,fontWeight:500,cursor:'pointer',fontFamily:'inherit' }
          }, 'Explorer l\'annuaire →'),
          React.createElement('button', {
            onClick: () => navigate('link'),
            style: { padding:'13px 22px',border:'1px solid var(--line)',borderRadius:10,background:'var(--surface)',
              color:'var(--ink)',fontSize:14.5,fontWeight:500,cursor:'pointer',fontFamily:'inherit' }
          }, 'Trouver un lien'),
        )
      ),

      /* Three feature cards */
      React.createElement('div', {
        style: { display:'grid',gridTemplateColumns:'repeat(auto-fit,minmax(260px,1fr))',gap:16 }
      },
        [
          { title:'Annuaire', desc:'Parcourez tous les étudiants en grille, liste, timeline ou arbre.', page:'directory',
            icon: 'M3 4h14M3 9h14M3 14h14' },
          { title:'Profil', desc:'Découvrez parrains, fillots, biographie et liens.', page:'profile',
            icon: 'M10 10a3 3 0 100-6 3 3 0 000 6zM2 17a8 8 0 0116 0' },
          { title:'Lien', desc:'Visualisez le chemin de parrainage entre 2 personnes.', page:'link',
            icon: 'M5 10h10M11 6l4 4-4 4' },
        ].map(card =>
          React.createElement('button', {
            key: card.title,
            onClick: () => navigate(card.page),
            style: { background:'var(--surface)',border:'1px solid var(--line)',borderRadius:14,padding:24,
              textAlign:'left',cursor:'pointer',transition:'all 0.18s',fontFamily:'inherit',color:'var(--ink)' },
            onMouseEnter: e => { e.currentTarget.style.borderColor='var(--ink)'; e.currentTarget.style.transform='translateY(-2px)'; },
            onMouseLeave: e => { e.currentTarget.style.borderColor='var(--line)'; e.currentTarget.style.transform=''; }
          },
            React.createElement('div', {
              style: { width:36,height:36,borderRadius:9,background:'var(--bg)',display:'flex',alignItems:'center',justifyContent:'center',marginBottom:14 }
            },
              React.createElement('svg', { width:18,height:18,viewBox:'0 0 20 20',fill:'none',stroke:'var(--ink)',strokeWidth:1.5,strokeLinecap:'round',strokeLinejoin:'round' },
                React.createElement('path', { d: card.icon })
              )
            ),
            React.createElement('div', { style: { fontSize:15,fontWeight:600,marginBottom:6,letterSpacing:'-0.01em' } }, card.title),
            React.createElement('div', { style: { fontSize:13.5,color:'var(--ink-3)',lineHeight:1.5 } }, card.desc),
          )
        )
      ),

      /* Recent additions strip */
      React.createElement('div', { style: { marginTop:64 } },
        React.createElement('div', {
          style: { display:'flex',alignItems:'baseline',justifyContent:'space-between',marginBottom:18 }
        },
          React.createElement('h2', { style: { fontSize:18,fontWeight:600,letterSpacing:'-0.01em' } }, 'Promotions actives'),
          React.createElement('button', {
            onClick: () => navigate('directory'),
            style: { background:'none',border:'none',color:'var(--ink-3)',fontSize:13,cursor:'pointer',fontFamily:'inherit' }
          }, 'Voir tout →'),
        ),
        React.createElement('div', { style: { display:'grid',gridTemplateColumns:`repeat(${YEARS.length}, 1fr)`,gap:10 } },
          YEARS.map(y => {
            const c = PROMO_COLORS[y];
            const count = ALL_PERSONS.filter(p => p.startYear === y).length;
            return React.createElement('button', {
              key: y, onClick: () => navigate('directory'),
              style: { background:'var(--surface)',border:'1px solid var(--line)',borderRadius:12,padding:'14px 12px',
                cursor:'pointer',fontFamily:'inherit',textAlign:'left',transition:'border-color 0.15s' },
              onMouseEnter: e => e.currentTarget.style.borderColor = c,
              onMouseLeave: e => e.currentTarget.style.borderColor = 'var(--line)'
            },
              React.createElement('div', { style: { width:8,height:8,borderRadius:'50%',background:c,marginBottom:10 } }),
              React.createElement('div', { style: { fontSize:13,fontWeight:600,color:'var(--ink)' } }, y),
              React.createElement('div', { style: { fontSize:11.5,color:'var(--ink-3)',marginTop:2 } }, `${count} étudiants`)
            );
          })
        )
      )
    )
  );
}

/* ============================================================
 * DIRECTORY PAGE — Grid / List / Timeline / Tree
 * ============================================================ */
function GridView({ persons, onSelect, cardStyle, showPromoBar }) {
  return React.createElement('div', {
    style: { display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(180px,1fr))', gap:14 }
  }, persons.map((p,i) =>
    React.createElement('article', {
      key: p.id, onClick: () => onSelect(p),
      style: { background:'var(--surface)', borderRadius:14, overflow:'hidden', cursor:'pointer',
        transition:'all 0.18s ease', border:'1px solid var(--line)',
        animation: `cardIn 0.3s ease ${Math.min(i*20,300)}ms both` },
      onMouseEnter: e => { e.currentTarget.style.transform='translateY(-3px)'; e.currentTarget.style.borderColor=p.color; e.currentTarget.style.boxShadow=`0 8px 24px ${p.color}1A`; },
      onMouseLeave: e => { e.currentTarget.style.transform=''; e.currentTarget.style.borderColor='var(--line)'; e.currentTarget.style.boxShadow=''; }
    },
      showPromoBar && React.createElement('div', { style: { height:3, background:p.color } }),
      React.createElement('div', { style: { aspectRatio: cardStyle==='compact'?'4/3':'1/1', overflow:'hidden', background: p.color+'10' } },
        React.createElement(Avatar, { person: p, size: 999 })
      ),
      React.createElement('div', { style: { padding: cardStyle==='compact' ? '10px 12px' : '14px 14px' } },
        React.createElement('div', { style: { fontSize:13.5, fontWeight:600, color:'var(--ink)', lineHeight:1.2, letterSpacing:'-0.005em' } },
          p.firstName + ' ' + p.lastName),
        React.createElement('div', { style: { fontSize:11.5, color:'var(--ink-3)', marginTop:6,display:'flex',alignItems:'center',gap:6 } },
          React.createElement('span', { style: { width:6,height:6,borderRadius:'50%',background:p.color } }),
          `${p.startYear} / ${p.startYear+1}`)
      )
    )
  ));
}

function ListView({ persons, onSelect }) {
  return React.createElement('div', {
    style: { background:'var(--surface)',border:'1px solid var(--line)',borderRadius:12,overflow:'hidden' }
  },
    React.createElement('div', {
      style: { display:'grid',gridTemplateColumns:'44px 2fr 1fr 1fr 80px',gap:16,padding:'12px 18px',
        fontSize:11,fontWeight:600,textTransform:'uppercase',letterSpacing:'0.06em',color:'var(--ink-3)',
        borderBottom:'1px solid var(--line)',background:'var(--bg)' }
    },
      React.createElement('span'),
      React.createElement('span',null,'Nom'),
      React.createElement('span',null,'Promo'),
      React.createElement('span',null,'Ville'),
      React.createElement('span',{style:{textAlign:'right'}},'Liens')
    ),
    persons.map((p,i) => {
      const links = getGodFathers(p.id).length + getGodChildren(p.id).length;
      return React.createElement('div', {
        key: p.id, onClick: () => onSelect(p),
        style: { display:'grid',gridTemplateColumns:'44px 2fr 1fr 1fr 80px',gap:16,padding:'10px 18px',
          alignItems:'center',cursor:'pointer',transition:'background 0.12s',fontSize:13.5,
          borderBottom: i < persons.length-1 ? '1px solid var(--line)' : 'none' },
        onMouseEnter: e => e.currentTarget.style.background='var(--bg)',
        onMouseLeave: e => e.currentTarget.style.background='transparent'
      },
        React.createElement('div', null, React.createElement(Avatar, { person: p, size: 32, square: true })),
        React.createElement('span', { style: { fontWeight:500,color:'var(--ink)' } }, p.firstName + ' ' + p.lastName),
        React.createElement('span', { style: { color:'var(--ink-2)',display:'flex',alignItems:'center',gap:8 } },
          React.createElement('span', { style: { width:6,height:6,borderRadius:'50%',background:p.color } }),
          `${p.startYear}/${(p.startYear+1).toString().slice(2)}`
        ),
        React.createElement('span', { style: { color:'var(--ink-3)' } }, p.city),
        React.createElement('span', { style: { color:'var(--ink-3)',fontSize:12,textAlign:'right' } }, `${links} lien${links!==1?'s':''}`)
      );
    })
  );
}

function TimelineView({ persons, onSelect }) {
  const grouped = useMemo(() => {
    const map = {};
    persons.forEach(p => { if (!map[p.startYear]) map[p.startYear] = []; map[p.startYear].push(p); });
    return Object.entries(map).sort(([a],[b]) => a-b);
  }, [persons]);

  return React.createElement('div', { style: { display:'flex',flexDirection:'column',gap:32 } },
    grouped.map(([year, pList]) => {
      const c = PROMO_COLORS[year] || '#999';
      return React.createElement('div', { key: year },
        React.createElement('div', {
          style: { display:'flex',alignItems:'baseline',gap:14,marginBottom:14 }
        },
          React.createElement('h3', {
            style: { fontSize:22,fontWeight:600,color:'var(--ink)',letterSpacing:'-0.02em' }
          }, `Promo ${year}`),
          React.createElement('span', {
            style: { fontSize:13,color:'var(--ink-3)' }
          }, `${pList.length} étudiants · ${year} → ${parseInt(year)+1}`)
        ),
        React.createElement('div', {
          style: { display:'grid',gridTemplateColumns:'repeat(auto-fill,minmax(160px,1fr))',gap:8,
            paddingLeft:20,borderLeft:`2px solid ${c}` }
        },
          pList.map(p =>
            React.createElement('div', {
              key: p.id, onClick: () => onSelect(p),
              style: { display:'flex',alignItems:'center',gap:10,padding:'8px 12px',background:'var(--surface)',
                borderRadius:10,cursor:'pointer',transition:'all 0.15s',border:'1px solid var(--line)' },
              onMouseEnter: e => { e.currentTarget.style.borderColor=c; e.currentTarget.style.transform='translateX(3px)'; },
              onMouseLeave: e => { e.currentTarget.style.borderColor='var(--line)'; e.currentTarget.style.transform=''; }
            },
              React.createElement('div', { style: { flexShrink:0 } }, React.createElement(Avatar, { person: p, size: 32, square: true })),
              React.createElement('div', { style: { minWidth:0 } },
                React.createElement('div', { style: { fontSize:13,fontWeight:500,color:'var(--ink)',lineHeight:1.2,whiteSpace:'nowrap',overflow:'hidden',textOverflow:'ellipsis' } }, p.firstName),
                React.createElement('div', { style: { fontSize:12,color:'var(--ink-3)',whiteSpace:'nowrap',overflow:'hidden',textOverflow:'ellipsis' } }, p.lastName)
              )
            )
          )
        )
      );
    })
  );
}

/* ─── Tree / Graph view ─── */
function TreeView({ persons, onSelect, filterMode, showLabels, focusPersonId, setFocusPersonId }) {
  const containerRef = useRef(null);
  const [pan, setPan] = useState({ x: 0, y: 0 });
  const [zoom, setZoom] = useState(1);
  const dragRef = useRef({ active: false, startX: 0, startY: 0, originX: 0, originY: 0 });

  // Layout: nodes positioned by year (Y) and within-year index (X)
  const layout = useMemo(() => {
    const visibleIds = new Set(persons.map(p => p.id));
    let nodes = persons;
    let links = LINKS.filter(l => visibleIds.has(l.godFatherId) && visibleIds.has(l.godChildId));

    // Filter by connection
    if (filterMode === 'connected') {
      const linkedIds = new Set();
      links.forEach(l => { linkedIds.add(l.godFatherId); linkedIds.add(l.godChildId); });
      nodes = nodes.filter(p => linkedIds.has(p.id));
    }
    if (filterMode === 'isolated') {
      const linkedIds = new Set();
      LINKS.forEach(l => { linkedIds.add(l.godFatherId); linkedIds.add(l.godChildId); });
      nodes = nodes.filter(p => !linkedIds.has(p.id));
      links = [];
    }
    // Focus mode: only node + neighbors of neighbors
    if (focusPersonId) {
      const center = focusPersonId;
      const lvl1 = new Set([center]);
      LINKS.forEach(l => {
        if (l.godFatherId === center) lvl1.add(l.godChildId);
        if (l.godChildId === center) lvl1.add(l.godFatherId);
      });
      const lvl2 = new Set(lvl1);
      LINKS.forEach(l => {
        if (lvl1.has(l.godFatherId)) lvl2.add(l.godChildId);
        if (lvl1.has(l.godChildId)) lvl2.add(l.godFatherId);
      });
      nodes = ALL_PERSONS.filter(p => lvl2.has(p.id));
      links = LINKS.filter(l => lvl2.has(l.godFatherId) && lvl2.has(l.godChildId));
    }

    // ─── Indirect links: when one or more intermediates are hidden,
    // draw a dotted edge between the surviving ancestor & descendant.
    // Walk descendants from each visible node through hidden ones until
    // we hit another visible node.
    const visibleNodeIds = new Set(nodes.map(p => p.id));
    const childrenOf = {};
    LINKS.forEach(l => {
      if (!childrenOf[l.godFatherId]) childrenOf[l.godFatherId] = [];
      childrenOf[l.godFatherId].push(l.godChildId);
    });
    const directKey = new Set(links.map(l => `${l.godFatherId}-${l.godChildId}`));
    const indirectLinks = [];
    const indirectKey = new Set();
    nodes.forEach(src => {
      // BFS through hidden descendants
      const stack = [{ id: src.id, hops: 0 }];
      const seen = new Set([src.id]);
      while (stack.length) {
        const { id, hops } = stack.pop();
        const kids = childrenOf[id] || [];
        for (const childId of kids) {
          if (seen.has(childId)) continue;
          seen.add(childId);
          if (visibleNodeIds.has(childId)) {
            // Reached a visible descendant. Only count it as indirect
            // if we passed through ≥1 hidden intermediate.
            if (hops >= 1) {
              const k = `${src.id}-${childId}`;
              if (!directKey.has(k) && !indirectKey.has(k)) {
                indirectKey.add(k);
                indirectLinks.push({ godFatherId: src.id, godChildId: childId, hops: hops + 1 });
              }
            }
            // Don't traverse past a visible node — that path will be
            // covered by its own BFS.
          } else {
            stack.push({ id: childId, hops: hops + 1 });
          }
        }
      }
    });

    // Group nodes by year
    const byYear = {};
    nodes.forEach(p => { if (!byYear[p.startYear]) byYear[p.startYear] = []; byYear[p.startYear].push(p); });
    Object.keys(byYear).forEach(y => byYear[y].sort((a,b) => a.lastName.localeCompare(b.lastName)));

    const yearList = Object.keys(byYear).map(Number).sort((a,b) => a-b);
    const COL_W = 140;
    const ROW_H = 130;
    const positions = {};
    yearList.forEach((year, rowIdx) => {
      const list = byYear[year];
      list.forEach((p, colIdx) => {
        positions[p.id] = {
          x: colIdx * COL_W + 60,
          y: rowIdx * ROW_H + 80,
          year,
        };
      });
    });

    const maxCols = Math.max(...Object.values(byYear).map(l => l.length), 1);
    return {
      nodes, links, indirectLinks, positions, yearList,
      width: maxCols * COL_W + 120,
      height: yearList.length * ROW_H + 160,
    };
  }, [persons, filterMode, focusPersonId]);

  const handleMouseDown = (e) => {
    if (e.target.closest('[data-node]')) return;
    dragRef.current = {
      active: true, startX: e.clientX, startY: e.clientY,
      originX: pan.x, originY: pan.y,
    };
  };
  const handleMouseMove = (e) => {
    if (!dragRef.current.active) return;
    setPan({
      x: dragRef.current.originX + (e.clientX - dragRef.current.startX),
      y: dragRef.current.originY + (e.clientY - dragRef.current.startY),
    });
  };
  const handleMouseUp = () => { dragRef.current.active = false; };
  const handleWheel = (e) => {
    e.preventDefault();
    const factor = e.deltaY < 0 ? 1.1 : 0.9;
    setZoom(z => Math.max(0.3, Math.min(2.5, z * factor)));
  };

  return React.createElement('div', {
    ref: containerRef,
    onMouseDown: handleMouseDown, onMouseMove: handleMouseMove,
    onMouseUp: handleMouseUp, onMouseLeave: handleMouseUp, onWheel: handleWheel,
    style: {
      position:'relative',width:'100%',height:'calc(100vh - 240px)',minHeight:520,
      background:'var(--surface)',border:'1px solid var(--line)',borderRadius:14,overflow:'hidden',
      cursor: dragRef.current.active ? 'grabbing' : 'grab',
      backgroundImage: 'radial-gradient(circle, var(--line) 1px, transparent 1px)',
      backgroundSize: '24px 24px',
    }
  },
    /* Zoom controls */
    React.createElement('div', {
      style: { position:'absolute',top:14,right:14,display:'flex',flexDirection:'column',gap:6,zIndex:10 }
    },
      ['+','−','⤓'].map((sym, i) =>
        React.createElement('button', {
          key: sym, onClick: (e) => { e.stopPropagation();
            if (i===0) setZoom(z => Math.min(2.5, z*1.2));
            if (i===1) setZoom(z => Math.max(0.3, z/1.2));
            if (i===2) { setZoom(1); setPan({x:0,y:0}); setFocusPersonId(null); }
          },
          style: { width:32,height:32,border:'1px solid var(--line)',borderRadius:8,background:'var(--surface)',
            cursor:'pointer',fontSize:14,fontWeight:600,color:'var(--ink-2)',fontFamily:'inherit' }
        }, sym)
      )
    ),
    /* Legend / counter */
    React.createElement('div', {
      style: { position:'absolute',bottom:14,left:14,zIndex:10,fontSize:12,color:'var(--ink-3)',
        background:'var(--surface)',border:'1px solid var(--line)',padding:'7px 12px',borderRadius:8,
        display:'flex',gap:14,alignItems:'center' }
    },
      React.createElement('span', null, `${layout.nodes.length} nœuds · ${layout.links.length} liens${layout.indirectLinks.length ? ` · ${layout.indirectLinks.length} indirects` : ''}`),
      focusPersonId && React.createElement('button', {
        onClick: () => setFocusPersonId(null),
        style: { border:'none',background:'none',color:'var(--ink)',fontSize:12,cursor:'pointer',fontWeight:500,fontFamily:'inherit',textDecoration:'underline' }
      }, '× Vue complète'),
      React.createElement('span', { style: { color:'var(--ink-4)' } }, `${Math.round(zoom*100)}%`),
    ),

    /* Inner SVG canvas */
    React.createElement('div', {
      style: { transform:`translate(${pan.x}px, ${pan.y}px) scale(${zoom})`, transformOrigin:'0 0',
        width:layout.width,height:layout.height,position:'relative',transition: dragRef.current.active ? 'none' : 'transform 0.12s ease' }
    },
      /* Year guide lines */
      layout.yearList.map((year, rowIdx) => {
        const y = rowIdx * 130 + 80;
        const c = PROMO_COLORS[year] || '#999';
        return React.createElement('div', { key: year },
          React.createElement('div', {
            style: { position:'absolute',left:0,top:y-20,width:layout.width,height:1,background:`${c}30` }
          }),
          React.createElement('div', {
            style: { position:'absolute',left:8,top:y-32,fontSize:10,fontWeight:600,letterSpacing:'0.06em',
              textTransform:'uppercase',color:c,background:'var(--surface)',padding:'2px 8px',borderRadius:4 }
          }, `Promo ${year}`)
        );
      }),
      /* Edges (SVG layer) */
      React.createElement('svg', {
        style: { position:'absolute',inset:0,width:'100%',height:'100%',pointerEvents:'none' }
      },
        /* Indirect (transitive) links — drawn underneath, dashed */
        layout.indirectLinks.map((l, i) => {
          const a = layout.positions[l.godFatherId];
          const b = layout.positions[l.godChildId];
          if (!a || !b) return null;
          const ax = a.x + 30, ay = a.y + 30;
          const bx = b.x + 30, by = b.y + 30;
          const cy = (ay + by) / 2;
          const path = `M${ax},${ay} C${ax},${cy} ${bx},${cy} ${bx},${by}`;
          const c = PROMO_COLORS[a.year] || '#999';
          return React.createElement('path', {
            key: `i-${i}`, d: path, stroke: c, strokeWidth: 1.25, fill: 'none',
            strokeDasharray: '3 4', opacity: 0.5, strokeLinecap: 'round',
          });
        }),
        /* Direct links */
        layout.links.map((l,i) => {
          const a = layout.positions[l.godFatherId];
          const b = layout.positions[l.godChildId];
          if (!a || !b) return null;
          const ax = a.x + 30, ay = a.y + 30;
          const bx = b.x + 30, by = b.y + 30;
          const cy = (ay + by) / 2;
          const path = `M${ax},${ay} C${ax},${cy} ${bx},${cy} ${bx},${by}`;
          const c = PROMO_COLORS[a.year] || '#999';
          return React.createElement('path', {
            key: i, d: path, stroke: c, strokeWidth: 1.5, fill: 'none', opacity: 0.5,
          });
        })
      ),
      /* Nodes */
      layout.nodes.map(p => {
        const pos = layout.positions[p.id];
        if (!pos) return null;
        const isFocus = p.id === focusPersonId;
        return React.createElement('div', {
          key: p.id, 'data-node': true,
          onClick: e => { e.stopPropagation(); setFocusPersonId(p.id); onSelect(p); },
          onDoubleClick: e => { e.stopPropagation(); setFocusPersonId(p.id); },
          style: { position:'absolute',left:pos.x,top:pos.y,width:60,
            display:'flex',flexDirection:'column',alignItems:'center',gap:4,cursor:'pointer',
            transition:'transform 0.15s' },
          onMouseEnter: e => e.currentTarget.style.transform = 'scale(1.08)',
          onMouseLeave: e => e.currentTarget.style.transform = ''
        },
          React.createElement('div', {
            style: { width: isFocus ? 56 : 44, height: isFocus ? 56 : 44,
              borderRadius:'50%', overflow:'hidden', border:`${isFocus ? 3 : 2}px solid ${p.color}`,
              boxShadow: isFocus ? `0 0 0 4px ${p.color}30, 0 8px 20px ${p.color}30` : `0 2px 6px rgba(0,0,0,0.06)`,
              transition:'all 0.2s', background:'#fff' }
          },
            React.createElement(Avatar, { person: p, size: isFocus ? 56 : 44 })
          ),
          showLabels && React.createElement('div', {
            style: { fontSize:10.5,fontWeight:500,color:'var(--ink)',textAlign:'center',lineHeight:1.15,
              maxWidth:80,whiteSpace:'nowrap',overflow:'hidden',textOverflow:'ellipsis',
              background:'var(--surface)',padding:'2px 5px',borderRadius:4 }
          }, p.firstName)
        );
      })
    )
  );
}

function DirectoryPage({ navigate, t, setTweak }) {
  const [search, setSearch] = useState('');
  const [yearFilters, setYearFilters] = useState(() => new Set());
  const [sortAZ, setSortAZ] = useState(false);
  const [selected, setSelected] = useState(null);
  const [focusPersonId, setFocusPersonId] = useState(null);

  const toggleYear = (y, e) => {
    setYearFilters(prev => {
      const next = new Set(prev);
      // Shift+clic = ajout/retrait sans toucher au reste (déjà le comportement par défaut ici)
      // Clic simple = bascule
      if (next.has(y)) next.delete(y); else next.add(y);
      return next;
    });
  };
  const clearYears = () => setYearFilters(new Set());

  const filtered = useMemo(() => {
    let list = [...ALL_PERSONS];
    if (search) {
      const q = search.toLowerCase();
      list = list.filter(p => p.fullName.toLowerCase().includes(q));
    }
    if (yearFilters.size > 0) list = list.filter(p => yearFilters.has(p.startYear));
    if (sortAZ) list.sort((a,b) => a.lastName.localeCompare(b.lastName));
    return list;
  }, [search, yearFilters, sortAZ]);

  const view = t.directoryView;

  return React.createElement('div', { style: { background:'var(--bg)',minHeight:'calc(100vh - 60px)' } },
    React.createElement('div', { style: { maxWidth:1280,margin:'0 auto',padding:'28px 28px 80px' } },
      /* Header */
      React.createElement('div', { style: { marginBottom:24 } },
        React.createElement('h1', {
          style: { fontSize:30,fontWeight:600,color:'var(--ink)',letterSpacing:'-0.02em',marginBottom:6 }
        }, 'Annuaire'),
        React.createElement('p', { style: { fontSize:14,color:'var(--ink-3)' } },
          `${ALL_PERSONS.length} étudiants · ${YEARS.length} promotions · ${LINKS.length} liens de parrainage`),
      ),

      /* Toolbar */
      React.createElement('div', {
        style: { display:'flex',flexWrap:'wrap',alignItems:'center',gap:10,marginBottom:18 }
      },
        React.createElement('div', { style: { flex:'1 1 240px',position:'relative',minWidth:200 } },
          React.createElement('svg', {
            width:14,height:14,viewBox:'0 0 15 15',fill:'var(--ink-4)',
            style: { position:'absolute',left:14,top:'50%',transform:'translateY(-50%)' }
          }, React.createElement('path', {
            d:'M10 6.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0ZM9.38 10.44a5 5 0 1 1 1.06-1.06l3.07 3.07a.75.75 0 1 1-1.06 1.06L9.38 10.44Z',
            fillRule:'evenodd'
          })),
          React.createElement('input', {
            type:'text', value: search, onChange: e => setSearch(e.target.value),
            placeholder:'Rechercher…',
            style: { width:'100%',height:38,paddingLeft:36,paddingRight:14,border:'1px solid var(--line)',borderRadius:9,
              fontSize:13.5,outline:'none',background:'var(--surface)',color:'var(--ink)',transition:'border 0.15s',fontFamily:'inherit' }
          })
        ),
        /* View tabs */
        React.createElement('div', {
          style: { display:'flex',background:'var(--surface)',borderRadius:9,border:'1px solid var(--line)',padding:3,gap:2 }
        },
          [
            { v:'grid', l:'Grille', icon:'M3 3h6v6H3zm8 0h6v6h-6zm-8 8h6v6H3zm8 0h6v6h-6z' },
            { v:'list', l:'Liste', icon:'M3 4h14M3 9h14M3 14h14' },
            { v:'timeline', l:'Timeline', icon:'M5 3v14M5 5h10M5 10h7M5 15h10' },
            { v:'tree', l:'Arbre', icon:'M10 3v4m-4 4V7h8v4m-8 0v4m8-4v4M3 15h4m6 0h4' },
          ].map(opt =>
            React.createElement('button', {
              key: opt.v, onClick: () => setTweak('directoryView', opt.v),
              style: { padding:'7px 12px',border:'none',borderRadius:7,cursor:'pointer',fontSize:12.5,fontWeight:500,fontFamily:'inherit',
                background: view===opt.v ? 'var(--ink)' : 'transparent',
                color: view===opt.v ? '#fff' : 'var(--ink-2)', transition:'all 0.15s',display:'flex',alignItems:'center',gap:6 }
            },
              React.createElement('svg', { width:13,height:13,viewBox:'0 0 20 20',fill:'none',stroke:'currentColor',strokeWidth:1.5,strokeLinecap:'round',strokeLinejoin:'round' },
                React.createElement('path', { d: opt.icon })
              ),
              opt.l
            )
          )
        ),
        view !== 'tree' && React.createElement('button', {
          onClick: () => setSortAZ(v => !v),
          style: { height:38,padding:'0 14px',border:`1px solid ${sortAZ ? 'var(--ink)' : 'var(--line)'}`,borderRadius:9,
            background: sortAZ ? 'var(--ink)' : 'var(--surface)', color: sortAZ ? '#fff' : 'var(--ink-2)',
            fontSize:12.5,fontWeight:500,cursor:'pointer',fontFamily:'inherit',transition:'all 0.15s' }
        }, 'A → Z'),
      ),

      /* Year chips — multi-sélection */
      React.createElement('div', {
        style: { display:'flex',flexWrap:'wrap',gap:6,marginBottom:24,alignItems:'center' }
      },
        React.createElement('button', {
          onClick: clearYears,
          style: { padding:'5px 12px',borderRadius:18,border:`1px solid ${yearFilters.size===0 ? 'var(--ink)' : 'var(--line)'}`,
            cursor:'pointer',fontSize:12,fontWeight:500,fontFamily:'inherit',
            background: yearFilters.size===0 ? 'var(--ink)' : 'var(--surface)',
            color: yearFilters.size===0 ? '#fff' : 'var(--ink-2)', transition:'all 0.12s' }
        }, 'Toutes'),
        YEARS.map(y => {
          const c = PROMO_COLORS[y];
          const active = yearFilters.has(y);
          return React.createElement('button', {
            key: y, onClick: (e) => toggleYear(y, e),
            style: { padding:'5px 12px',borderRadius:18,
              border:`1px solid ${active ? c : 'var(--line)'}`,
              cursor:'pointer',fontSize:12,fontWeight:500,fontFamily:'inherit',
              background: active ? c : 'var(--surface)',
              color: active ? '#fff' : 'var(--ink-2)',
              display:'flex',alignItems:'center',gap:6,transition:'all 0.12s' }
          },
            React.createElement('span', {
              style: {
                width:12,height:12,borderRadius:3,
                border:`1.5px solid ${active ? '#fff' : c}`,
                background: active ? '#fff' : 'transparent',
                display:'flex',alignItems:'center',justifyContent:'center',
                transition:'all 0.12s'
              }
            },
              active && React.createElement('svg', {
                width:8,height:8,viewBox:'0 0 10 10',fill:'none',stroke:c,strokeWidth:2,strokeLinecap:'round',strokeLinejoin:'round'
              }, React.createElement('path', { d:'M2 5.5 4 7.5 8 3' }))
            ),
            `${y} / ${(y+1).toString().slice(2)}`
          );
        }),
        yearFilters.size > 1 && React.createElement('span', {
          style: { fontSize:11.5,color:'var(--ink-3)',marginLeft:4 }
        }, `${yearFilters.size} promos sélectionnées`)
      ),

      /* Tree-specific filter row */
      view === 'tree' && React.createElement('div', {
        style: { display:'flex',gap:8,marginBottom:14,fontSize:12.5,alignItems:'center' }
      },
        React.createElement('span', { style: { color:'var(--ink-3)',marginRight:4 } }, 'Afficher :'),
        [
          { v:'all', l:'Tous' },
          { v:'connected', l:'Avec liens' },
          { v:'isolated', l:'Sans liens' },
        ].map(o =>
          React.createElement('button', {
            key: o.v, onClick: () => setTweak('treeFilterMode', o.v),
            style: { padding:'5px 12px',borderRadius:18,
              border: `1px solid ${t.treeFilterMode===o.v ? 'var(--ink)' : 'var(--line)'}`,
              background: t.treeFilterMode===o.v ? 'var(--ink)' : 'var(--surface)',
              color: t.treeFilterMode===o.v ? '#fff' : 'var(--ink-2)',
              fontSize:12,fontWeight:500,cursor:'pointer',fontFamily:'inherit' }
          }, o.l)
        ),
        React.createElement('button', {
          onClick: () => setTweak('treeShowLabels', !t.treeShowLabels),
          style: { marginLeft:'auto',padding:'5px 12px',borderRadius:18,
            border: `1px solid ${t.treeShowLabels ? 'var(--ink)' : 'var(--line)'}`,
            background: t.treeShowLabels ? 'var(--ink)' : 'var(--surface)',
            color: t.treeShowLabels ? '#fff' : 'var(--ink-2)',
            fontSize:12,fontWeight:500,cursor:'pointer',fontFamily:'inherit' }
        }, t.treeShowLabels ? '✓ Noms' : 'Noms'),
      ),

      /* Count */
      view !== 'tree' && React.createElement('div', {
        style: { fontSize:12.5,color:'var(--ink-3)',marginBottom:14,fontWeight:500 }
      }, `${filtered.length} résultat${filtered.length>1?'s':''}`),

      /* View body */
      view === 'grid' && React.createElement(GridView, { persons: filtered, onSelect: setSelected, cardStyle: t.cardStyle, showPromoBar: t.showPromoBar }),
      view === 'list' && React.createElement(ListView, { persons: filtered, onSelect: setSelected }),
      view === 'timeline' && React.createElement(TimelineView, { persons: filtered, onSelect: setSelected }),
      view === 'tree' && React.createElement(TreeView, {
        persons: filtered, onSelect: setSelected,
        filterMode: t.treeFilterMode, showLabels: t.treeShowLabels,
        focusPersonId, setFocusPersonId,
      }),
    ),
    React.createElement(PersonModal, { person: selected, onClose: () => setSelected(null),
      onOpenProfile: (id) => navigate('profile', { id }) })
  );
}

/* ============================================================
 * PROFILE PAGE
 * ============================================================ */
function FamilyChip({ person, label, onClick, onLinkClick }) {
  return React.createElement('div', {
    style: { display:'flex',alignItems:'center',gap:10,padding:'8px 12px 8px 8px',
      background:'var(--surface)',border:'1px solid var(--line)',borderRadius:12,
      fontFamily:'inherit',transition:'all 0.15s',textAlign:'left',cursor:'pointer' },
    onMouseEnter: e => { e.currentTarget.style.borderColor=person.color; e.currentTarget.style.transform='translateY(-1px)'; },
    onMouseLeave: e => { e.currentTarget.style.borderColor='var(--line)'; e.currentTarget.style.transform=''; },
    onClick
  },
    React.createElement(Avatar, { person, size: 36, square: true }),
    React.createElement('div', { style: { flex:1 } },
      React.createElement('div', { style: { fontSize:13,fontWeight:500,color:'var(--ink)',lineHeight:1.2 } }, person.firstName + ' ' + person.lastName),
      React.createElement('div', { style: { fontSize:11.5,color:person.color,fontWeight:500,marginTop:2 } }, `Promo ${person.startYear}`),
    ),
    onLinkClick && React.createElement('button', {
      onClick: e => { e.stopPropagation(); onLinkClick(); },
      title: 'Voir ce parrainage',
      style: { width:24,height:24,border:'1px solid var(--line)',borderRadius:6,background:'var(--bg)',
        cursor:'pointer',color:'var(--ink-3)',fontSize:11,fontFamily:'inherit',
        display:'flex',alignItems:'center',justifyContent:'center' }
    }, '↗')
  );
}

function ProfilePage({ navigate, personId }) {
  const person = ALL_PERSONS.find(p => p.id === personId) || ALL_PERSONS[3];
  const fathers = getGodFathers(person.id);
  const children = getGodChildren(person.id);
  const [ancestorDepth, setAncestorDepth] = useState(1);
  const [descendantDepth, setDescendantDepth] = useState(1);

  // Walk up/down N generations. Returns array of arrays:
  // ancestorGens[0] = direct parents, [1] = grandparents, ...
  const ancestorGens = useMemo(() => {
    const gens = [];
    let frontier = [person.id];
    const seen = new Set([person.id]);
    for (let d = 0; d < ancestorDepth; d++) {
      const next = [];
      const nextIds = new Set();
      frontier.forEach(id => {
        getGodFathers(id).forEach(f => {
          if (!seen.has(f.id) && !nextIds.has(f.id)) {
            nextIds.add(f.id); next.push(f);
          }
        });
      });
      if (next.length === 0) break;
      next.forEach(p => seen.add(p.id));
      gens.push(next);
      frontier = next.map(p => p.id);
    }
    return gens;
  }, [person.id, ancestorDepth]);

  const descendantGens = useMemo(() => {
    const gens = [];
    let frontier = [person.id];
    const seen = new Set([person.id]);
    for (let d = 0; d < descendantDepth; d++) {
      const next = [];
      const nextIds = new Set();
      frontier.forEach(id => {
        getGodChildren(id).forEach(c => {
          if (!seen.has(c.id) && !nextIds.has(c.id)) {
            nextIds.add(c.id); next.push(c);
          }
        });
      });
      if (next.length === 0) break;
      next.forEach(p => seen.add(p.id));
      gens.push(next);
      frontier = next.map(p => p.id);
    }
    return gens;
  }, [person.id, descendantDepth]);

  // Are there potentially more generations beyond what we show?
  const moreAncestors = ancestorGens.length === ancestorDepth &&
    ancestorGens[ancestorGens.length - 1]?.some(p => getGodFathers(p.id).length > 0);
  const moreDescendants = descendantGens.length === descendantDepth &&
    descendantGens[descendantGens.length - 1]?.some(p => getGodChildren(p.id).length > 0);

  const genLabel = (offset, dir) => {
    if (offset === 1) return dir === 'up' ? 'Parrains' : 'Fillots';
    if (offset === 2) return dir === 'up' ? 'Grands-parrains' : 'Petits-fillots';
    return dir === 'up' ? `Génération +${offset}` : `Génération −${offset}`;
  };

  return React.createElement('div', { style: { background:'var(--bg)',minHeight:'calc(100vh - 60px)' } },
    React.createElement('div', { style: { maxWidth:980,margin:'0 auto',padding:'28px 28px 80px' } },
      /* Breadcrumb */
      React.createElement('div', {
        style: { fontSize:13,color:'var(--ink-3)',marginBottom:20,display:'flex',gap:6,alignItems:'center' }
      },
        React.createElement('a', { href:'#', onClick: e => { e.preventDefault(); navigate('directory'); },
          style: { color:'var(--ink-3)',textDecoration:'none' } }, 'Annuaire'),
        React.createElement('span', null, '/'),
        React.createElement('span', { style: { color:'var(--ink)' } }, person.firstName + ' ' + person.lastName),
      ),

      /* Hero card */
      React.createElement('div', {
        style: { background:'var(--surface)',border:'1px solid var(--line)',borderRadius:16,
          padding:32,marginBottom:24,position:'relative',overflow:'hidden' }
      },
        /* Color accent */
        React.createElement('div', {
          style: { position:'absolute',inset:0,background:`linear-gradient(135deg, ${person.color}10 0%, transparent 60%)`,pointerEvents:'none' }
        }),
        React.createElement('div', {
          style: { display:'flex',gap:28,alignItems:'flex-start',flexWrap:'wrap',position:'relative' }
        },
          React.createElement('div', { style: { flexShrink:0,borderRadius:18,overflow:'hidden',border:`3px solid ${person.color}`,boxShadow:`0 12px 32px ${person.color}30` } },
            React.createElement(Avatar, { person, size: 140, square: true })
          ),
          React.createElement('div', { style: { flex:'1 1 280px' } },
            React.createElement('div', {
              style: { display:'inline-flex',alignItems:'center',gap:6,fontSize:12,fontWeight:500,color:person.color,
                background:`${person.color}15`,padding:'4px 10px',borderRadius:16,marginBottom:12 }
            },
              React.createElement('span', { style: { width:6,height:6,borderRadius:'50%',background:person.color } }),
              `Promo ${person.startYear} / ${person.startYear+1}`
            ),
            React.createElement('h1', {
              style: { fontSize:36,fontWeight:600,color:'var(--ink)',letterSpacing:'-0.025em',lineHeight:1.1,marginBottom:8 }
            }, person.firstName + ' ' + person.lastName),
            React.createElement('div', { style: { fontSize:14,color:'var(--ink-3)',marginBottom:18 } },
              `${person.city} · ID #${person.id}`),
            person.biography && React.createElement('p', {
              style: { fontSize:14.5,color:'var(--ink-2)',lineHeight:1.6,marginBottom:18,maxWidth:540 }
            }, person.biography),
            React.createElement('div', { style: { display:'flex',gap:6,flexWrap:'wrap' } },
              person.tags.map(tag =>
                React.createElement('span', {
                  key: tag,
                  style: { fontSize:12,padding:'4px 10px',background:'var(--bg)',border:'1px solid var(--line)',
                    borderRadius:14,color:'var(--ink-2)' }
                }, tag)
              )
            )
          ),
          React.createElement('div', {
            style: { display:'flex',flexDirection:'column',gap:8,minWidth:140 }
          },
            React.createElement('button', {
              onClick: () => navigate('link', { fromId: 1, toId: person.id }),
              style: { padding:'10px 14px',border:'none',borderRadius:9,background:'var(--ink)',color:'#fff',
                fontSize:13,fontWeight:500,cursor:'pointer',fontFamily:'inherit' }
            }, 'Lien avec moi'),
            React.createElement('button', {
              style: { padding:'10px 14px',border:'1px solid var(--line)',borderRadius:9,background:'transparent',
                color:'var(--ink-2)',fontSize:13,fontWeight:500,cursor:'pointer',fontFamily:'inherit' }
            }, 'Modifier'),
          )
        ),
      ),

      /* Stats */
      React.createElement('div', {
        style: { display:'grid',gridTemplateColumns:'repeat(auto-fit,minmax(180px,1fr))',gap:12,marginBottom:24 }
      },
        [
          { l: 'Parrains', v: fathers.length },
          { l: 'Fillots', v: children.length },
          { l: 'Famille (étendue)', v: fathers.length + children.length },
          { l: 'Année de promo', v: person.startYear },
        ].map(s =>
          React.createElement('div', {
            key: s.l,
            style: { background:'var(--surface)',border:'1px solid var(--line)',borderRadius:12,padding:'16px 18px' }
          },
            React.createElement('div', { style: { fontSize:11,color:'var(--ink-3)',textTransform:'uppercase',letterSpacing:'0.06em',fontWeight:500 } }, s.l),
            React.createElement('div', { style: { fontSize:26,fontWeight:600,color:'var(--ink)',marginTop:4,letterSpacing:'-0.02em' } }, s.v),
          )
        )
      ),

      /* Family — interactive mini-graph (pan / zoom / edges) */
      React.createElement('div', {
        style: { background:'var(--surface)',border:'1px solid var(--line)',borderRadius:16,padding:24,marginBottom:24 }
      },
        React.createElement('div', {
          style: { display:'flex',alignItems:'center',justifyContent:'space-between',marginBottom:16,gap:12,flexWrap:'wrap' }
        },
          React.createElement('h2', {
            style: { fontSize:18,fontWeight:600,color:'var(--ink)',letterSpacing:'-0.01em' }
          }, 'Arbre familial'),
          React.createElement('div', { style: { display:'flex',gap:8,flexWrap:'wrap',alignItems:'center' } },
            /* Ancestor controls */
            React.createElement('div', {
              style: { display:'flex',alignItems:'center',gap:4,background:'var(--bg)',border:'1px solid var(--line)',borderRadius:18,padding:'2px 4px' }
            },
              React.createElement('span', { style:{ fontSize:11,color:'var(--ink-3)',padding:'0 6px' } }, '↑ Amont'),
              React.createElement('button', {
                onClick: () => setAncestorDepth(d => Math.max(0, d-1)),
                disabled: ancestorDepth === 0,
                style: { width:22,height:22,border:'none',borderRadius:'50%',background:'transparent',
                  cursor: ancestorDepth===0 ? 'default' : 'pointer',
                  color: ancestorDepth===0 ? 'var(--ink-4)' : 'var(--ink-2)',
                  fontFamily:'inherit',fontSize:13,fontWeight:600 }
              }, '−'),
              React.createElement('span', { style:{ fontSize:12,fontWeight:600,color:'var(--ink)',minWidth:14,textAlign:'center' } }, ancestorDepth),
              React.createElement('button', {
                onClick: () => setAncestorDepth(d => d+1),
                disabled: !moreAncestors,
                title: moreAncestors ? '' : 'Aucune génération supplémentaire',
                style: { width:22,height:22,border:'none',borderRadius:'50%',background:'transparent',
                  cursor: !moreAncestors ? 'default' : 'pointer',
                  color: !moreAncestors ? 'var(--ink-4)' : person.color,
                  fontFamily:'inherit',fontSize:13,fontWeight:600 }
              }, '+'),
            ),
            /* Descendant controls */
            React.createElement('div', {
              style: { display:'flex',alignItems:'center',gap:4,background:'var(--bg)',border:'1px solid var(--line)',borderRadius:18,padding:'2px 4px' }
            },
              React.createElement('span', { style:{ fontSize:11,color:'var(--ink-3)',padding:'0 6px' } }, '↓ Aval'),
              React.createElement('button', {
                onClick: () => setDescendantDepth(d => Math.max(0, d-1)),
                disabled: descendantDepth === 0,
                style: { width:22,height:22,border:'none',borderRadius:'50%',background:'transparent',
                  cursor: descendantDepth===0 ? 'default' : 'pointer',
                  color: descendantDepth===0 ? 'var(--ink-4)' : 'var(--ink-2)',
                  fontFamily:'inherit',fontSize:13,fontWeight:600 }
              }, '−'),
              React.createElement('span', { style:{ fontSize:12,fontWeight:600,color:'var(--ink)',minWidth:14,textAlign:'center' } }, descendantDepth),
              React.createElement('button', {
                onClick: () => setDescendantDepth(d => d+1),
                disabled: !moreDescendants,
                title: moreDescendants ? '' : 'Aucune génération supplémentaire',
                style: { width:22,height:22,border:'none',borderRadius:'50%',background:'transparent',
                  cursor: !moreDescendants ? 'default' : 'pointer',
                  color: !moreDescendants ? 'var(--ink-4)' : person.color,
                  fontFamily:'inherit',fontSize:13,fontWeight:600 }
              }, '+'),
            ),
          )
        ),
        ancestorGens.length === 0 && descendantGens.length === 0
          ? React.createElement('div', {
              style: { fontSize:13,color:'var(--ink-4)',textAlign:'center',padding:'40px 0' }
            }, 'Pas de parrains ni de fillots enregistrés.')
          : React.createElement(window.FamilyGraph, {
              person, ancestorDepth, descendantDepth, navigate
            })
      )
    )
  );
}

/* ============================================================
 * LINK PAGE — find path between two persons
 * ============================================================ */
function PersonPicker({ value, onChange, label, placeholder }) {
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState('');
  const ref = useRef(null);
  useEffect(() => {
    const h = e => { if (ref.current && !ref.current.contains(e.target)) setOpen(false); };
    document.addEventListener('mousedown', h);
    return () => document.removeEventListener('mousedown', h);
  }, []);
  const filtered = ALL_PERSONS.filter(p => p.fullName.toLowerCase().includes(query.toLowerCase())).slice(0, 8);
  const selected = value ? ALL_PERSONS.find(p => p.id === value) : null;

  return React.createElement('div', { ref, style: { position:'relative',flex:'1 1 240px' } },
    React.createElement('label', {
      style: { fontSize:11,color:'var(--ink-3)',textTransform:'uppercase',letterSpacing:'0.06em',fontWeight:500,display:'block',marginBottom:6 }
    }, label),
    React.createElement('button', {
      onClick: () => setOpen(o => !o),
      style: { width:'100%',padding:'12px 14px',border:'1px solid var(--line)',borderRadius:10,background:'var(--surface)',
        cursor:'pointer',fontFamily:'inherit',fontSize:14,textAlign:'left',
        display:'flex',alignItems:'center',gap:12,color:'var(--ink)' }
    },
      selected ? [
        React.createElement('div', { key:'a', style: { flexShrink:0 } }, React.createElement(Avatar, { person: selected, size: 32, square: true })),
        React.createElement('div', { key:'b', style: { flex:1 } },
          React.createElement('div', { style: { fontSize:13.5,fontWeight:500 } }, selected.firstName + ' ' + selected.lastName),
          React.createElement('div', { style: { fontSize:11.5,color:'var(--ink-3)' } }, `Promo ${selected.startYear}`),
        )
      ] : React.createElement('span', { style: { color:'var(--ink-4)' } }, placeholder),
      React.createElement('span', { style: { color:'var(--ink-4)',marginLeft:'auto',fontSize:14 } }, '▾')
    ),
    open && React.createElement('div', {
      style: { position:'absolute',top:'calc(100% + 6px)',left:0,right:0,zIndex:30,
        background:'var(--surface)',border:'1px solid var(--line)',borderRadius:10,
        boxShadow:'0 12px 32px rgba(0,0,0,0.08)',overflow:'hidden' }
    },
      React.createElement('input', {
        type:'text', autoFocus:true, value: query, onChange: e => setQuery(e.target.value),
        placeholder:'Rechercher…',
        style: { width:'100%',padding:'10px 14px',border:'none',borderBottom:'1px solid var(--line)',
          fontSize:13,outline:'none',fontFamily:'inherit',background:'transparent',color:'var(--ink)' }
      }),
      React.createElement('div', { style: { maxHeight:280,overflowY:'auto' } },
        filtered.map(p =>
          React.createElement('button', {
            key: p.id, onClick: () => { onChange(p.id); setOpen(false); setQuery(''); },
            style: { display:'flex',width:'100%',gap:10,padding:'8px 14px',border:'none',background:'transparent',
              cursor:'pointer',alignItems:'center',fontFamily:'inherit',textAlign:'left' },
            onMouseEnter: e => e.currentTarget.style.background = 'var(--bg)',
            onMouseLeave: e => e.currentTarget.style.background = 'transparent'
          },
            React.createElement(Avatar, { person: p, size: 28, square: true }),
            React.createElement('div', null,
              React.createElement('div', { style: { fontSize:13,color:'var(--ink)' } }, p.firstName + ' ' + p.lastName),
              React.createElement('div', { style: { fontSize:11,color:'var(--ink-3)' } }, `Promo ${p.startYear}`)
            )
          )
        )
      )
    )
  );
}

function LinkPage({ navigate, initialFromId, initialToId }) {
  const [fromId, setFromId] = useState(initialFromId || 1);
  const [toId, setToId] = useState(initialToId || 50);

  const path = useMemo(() => fromId && toId ? findPath(fromId, toId) : null, [fromId, toId]);
  const fromPerson = ALL_PERSONS.find(p => p.id === fromId);
  const toPerson = ALL_PERSONS.find(p => p.id === toId);

  return React.createElement('div', { style: { background:'var(--bg)',minHeight:'calc(100vh - 60px)' } },
    React.createElement('div', { style: { maxWidth:980,margin:'0 auto',padding:'28px 28px 80px' } },
      React.createElement('div', { style: { marginBottom:24 } },
        React.createElement('h1', {
          style: { fontSize:30,fontWeight:600,color:'var(--ink)',letterSpacing:'-0.02em',marginBottom:6 }
        }, 'Lien entre 2 personnes'),
        React.createElement('p', { style: { fontSize:14,color:'var(--ink-3)' } },
          'Choisissez deux personnes pour visualiser le chemin de parrainage qui les relie.'),
      ),

      /* Pickers */
      React.createElement('div', {
        style: { background:'var(--surface)',border:'1px solid var(--line)',borderRadius:14,padding:20,marginBottom:24,
          display:'flex',gap:16,alignItems:'flex-end',flexWrap:'wrap' }
      },
        React.createElement(PersonPicker, { value: fromId, onChange: setFromId, label:'De', placeholder:'Personne A' }),
        React.createElement('div', {
          style: { display:'flex',alignItems:'center',justifyContent:'center',width:36,height:44,marginBottom:0,color:'var(--ink-3)' }
        },
          React.createElement('button', {
            onClick: () => { const t = fromId; setFromId(toId); setToId(t); },
            style: { width:32,height:32,border:'1px solid var(--line)',borderRadius:8,background:'var(--bg)',
              cursor:'pointer',color:'var(--ink-2)',fontSize:14,fontFamily:'inherit' }
          }, '⇄')
        ),
        React.createElement(PersonPicker, { value: toId, onChange: setToId, label:'À', placeholder:'Personne B' }),
      ),

      /* Result */
      path ? React.createElement('div', {
        style: { background:'var(--surface)',border:'1px solid var(--line)',borderRadius:14,padding:32 }
      },
        React.createElement('div', {
          style: { display:'flex',alignItems:'baseline',gap:14,marginBottom:28 }
        },
          React.createElement('div', null,
            React.createElement('div', { style: { fontSize:11,color:'var(--ink-3)',textTransform:'uppercase',letterSpacing:'0.06em',fontWeight:500 } }, 'Distance'),
            React.createElement('div', { style: { fontSize:30,fontWeight:600,color:'var(--ink)',letterSpacing:'-0.02em' } }, path.length - 1),
          ),
          React.createElement('div', { style: { color:'var(--ink-3)',fontSize:14 } },
            path.length === 1 ? 'C\'est la même personne'
            : path.length === 2 ? 'Lien direct (parrain ↔ fillot)'
            : `${path.length - 1} étapes pour relier ces deux personnes`),
        ),

        /* Path chain */
        React.createElement('div', {
          style: { display:'flex',flexWrap:'wrap',alignItems:'center',gap:10 }
        },
          path.map((id, i) => {
            const p = ALL_PERSONS.find(x => x.id === id);
            return React.createElement(React.Fragment, { key: id },
              React.createElement('button', {
                onClick: () => navigate('profile', { id: p.id }),
                style: { display:'flex',alignItems:'center',gap:10,padding:'10px 14px 10px 10px',
                  background: i===0 || i===path.length-1 ? `${p.color}15` : 'var(--bg)',
                  border:`1px solid ${i===0 || i===path.length-1 ? p.color+'60' : 'var(--line)'}`,
                  borderRadius:14,cursor:'pointer',fontFamily:'inherit',transition:'all 0.15s' },
                onMouseEnter: e => e.currentTarget.style.transform = 'translateY(-2px)',
                onMouseLeave: e => e.currentTarget.style.transform = ''
              },
                React.createElement(Avatar, { person: p, size: 36, square: true }),
                React.createElement('div', { style: { textAlign:'left' } },
                  React.createElement('div', { style: { fontSize:13,fontWeight:500,color:'var(--ink)',lineHeight:1.2 } }, p.firstName + ' ' + p.lastName),
                  React.createElement('div', { style: { fontSize:11.5,color:'var(--ink-3)',marginTop:2 } }, `Promo ${p.startYear}`),
                )
              ),
              i < path.length - 1 && React.createElement('div', {
                style: { display:'flex',alignItems:'center',gap:6,color:'var(--ink-4)' }
              },
                React.createElement('div', { style: { width:24,height:1,background:'var(--ink-4)' } }),
                React.createElement('span', { style: { fontSize:11,fontWeight:500 } },
                  (() => {
                    const a = path[i], b = path[i+1];
                    const isFatherOf = LINKS.some(l => l.godFatherId === a && l.godChildId === b);
                    return isFatherOf ? 'parraine' : 'fillot de';
                  })()
                ),
                React.createElement('div', { style: { width:24,height:1,background:'var(--ink-4)' } }),
              )
            );
          })
        ),

        /* Visual SVG path */
        React.createElement('div', {
          style: { marginTop:32,paddingTop:24,borderTop:'1px solid var(--line)' }
        },
          React.createElement('div', {
            style: { fontSize:11,color:'var(--ink-3)',textTransform:'uppercase',letterSpacing:'0.06em',fontWeight:500,marginBottom:14 }
          }, 'Visualisation'),
          React.createElement('svg', {
            width:'100%',height: 180, viewBox:`0 0 ${Math.max(path.length*120, 400)} 180`,
            style: { display:'block' }
          },
            path.map((id, i) => {
              const p = ALL_PERSONS.find(x => x.id === id);
              const x = 60 + i * ((Math.max(path.length*120,400) - 120) / Math.max(path.length-1,1));
              const y = 90;
              return React.createElement('g', { key: id },
                i < path.length - 1 && React.createElement('line', {
                  x1: x + 22, y1: y,
                  x2: 60 + (i+1) * ((Math.max(path.length*120,400) - 120) / Math.max(path.length-1,1)) - 22, y2: y,
                  stroke: 'var(--ink-4)', strokeWidth: 1.5, strokeDasharray: '4 4'
                }),
                React.createElement('circle', { cx: x, cy: y, r: 22, fill: p.color, opacity: 0.15 }),
                React.createElement('circle', { cx: x, cy: y, r: 22, fill: 'none', stroke: p.color, strokeWidth: 2 }),
                React.createElement('text', {
                  x, y: y + 4, textAnchor: 'middle', fontSize: 11, fontWeight: 600, fill: p.color, fontFamily: 'inherit'
                }, (p.firstName[0] + p.lastName[0]).toUpperCase()),
                React.createElement('text', {
                  x, y: y + 48, textAnchor: 'middle', fontSize: 11, fill: 'var(--ink-2)', fontFamily: 'inherit'
                }, p.firstName),
                React.createElement('text', {
                  x, y: y + 62, textAnchor: 'middle', fontSize: 10, fill: 'var(--ink-3)', fontFamily: 'inherit'
                }, p.lastName.toUpperCase()),
              );
            })
          )
        )
      ) : React.createElement('div', {
        style: { background:'var(--surface)',border:'1px dashed var(--line)',borderRadius:14,padding:48,textAlign:'center' }
      },
        React.createElement('div', { style: { fontSize:36,marginBottom:8,color:'var(--ink-4)' } }, '∅'),
        React.createElement('div', { style: { fontSize:15,fontWeight:500,color:'var(--ink)',marginBottom:6 } },
          'Aucun lien trouvé'),
        React.createElement('div', { style: { fontSize:13,color:'var(--ink-3)' } },
          fromPerson && toPerson
            ? `${fromPerson.firstName} et ${toPerson.firstName} ne sont pas reliés par une chaîne de parrainage.`
            : 'Sélectionnez deux personnes pour commencer.')
      )
    )
  );
}

/* ============================================================
 * SPONSORSHIP PAGE — details of a single link between 2 persons
 * ============================================================ */
function SponsorshipPage({ navigate, fromId, toId }) {
  const link = findLink(fromId, toId);
  const father = link ? ALL_PERSONS.find(p => p.id === link.godFatherId) : null;
  const child = link ? ALL_PERSONS.find(p => p.id === link.godChildId) : null;

  if (!link || !father || !child) {
    return React.createElement('div', {
      style: { background:'var(--bg)',minHeight:'calc(100vh - 60px)',padding:'60px 28px' }
    },
      React.createElement('div', {
        style: { maxWidth:600,margin:'0 auto',background:'var(--surface)',border:'1px dashed var(--line)',
          borderRadius:14,padding:48,textAlign:'center' }
      },
        React.createElement('div', { style: { fontSize:36,marginBottom:8,color:'var(--ink-4)' } }, '∅'),
        React.createElement('div', { style: { fontSize:15,fontWeight:500,color:'var(--ink)',marginBottom:6 } },
          'Aucun parrainage trouvé'),
        React.createElement('div', { style: { fontSize:13,color:'var(--ink-3)',marginBottom:20 } },
          'Ces deux personnes ne sont pas directement liées par un parrainage.'),
        React.createElement('button', {
          onClick: () => navigate('link', { fromId, toId }),
          style: { padding:'10px 16px',border:'1px solid var(--line)',borderRadius:9,background:'var(--surface)',
            color:'var(--ink)',fontSize:13,fontWeight:500,cursor:'pointer',fontFamily:'inherit' }
        }, 'Voir le chemin entre eux →'),
      )
    );
  }

  const TYPE_META = {
    iut:     { label:'IUT',     desc:'Parrainage officiel attribué à l\'intégration', color:'#2196F3', icon:'🎓' },
    coeur:   { label:'De cœur', desc:'Parrainage spontané, choisi mutuellement',     color:'#E85D75', icon:'♥' },
    faluche: { label:'Faluche', desc:'Parrainage faluchard',                          color:'#F4A236', icon:'⭑' },
    autre:   { label:'Autre',   desc:'Parrainage informel',                           color:'#8a8f97', icon:'•' },
  };
  const meta = TYPE_META[link.type] || TYPE_META.autre;
  const date = new Date(link.date);
  const dateStr = date.toLocaleDateString('fr-FR', { day:'numeric', month:'long', year:'numeric' });

  return React.createElement('div', { style: { background:'var(--bg)',minHeight:'calc(100vh - 60px)' } },
    React.createElement('div', { style: { maxWidth:880,margin:'0 auto',padding:'28px 28px 80px' } },
      /* Breadcrumb */
      React.createElement('div', {
        style: { fontSize:13,color:'var(--ink-3)',marginBottom:20,display:'flex',gap:6,alignItems:'center' }
      },
        React.createElement('a', { href:'#', onClick: e => { e.preventDefault(); navigate('directory'); },
          style: { color:'var(--ink-3)',textDecoration:'none' } }, 'Annuaire'),
        React.createElement('span', null, '/'),
        React.createElement('a', { href:'#', onClick: e => { e.preventDefault(); navigate('profile', { id: father.id }); },
          style: { color:'var(--ink-3)',textDecoration:'none' } }, father.firstName + ' ' + father.lastName),
        React.createElement('span', null, '/'),
        React.createElement('span', { style: { color:'var(--ink)' } }, 'Parrainage'),
      ),

      /* Header */
      React.createElement('div', {
        style: { display:'flex',alignItems:'center',gap:14,marginBottom:8 }
      },
        React.createElement('div', {
          style: { display:'inline-flex',alignItems:'center',gap:8,fontSize:12,fontWeight:500,
            color:meta.color,background:`${meta.color}15`,padding:'5px 12px',borderRadius:18 }
        },
          React.createElement('span', null, meta.icon),
          `Parrainage ${meta.label}`
        ),
        link.validated && React.createElement('span', {
          style: { display:'inline-flex',alignItems:'center',gap:6,fontSize:12,color:'#48BFA0',fontWeight:500 }
        },
          React.createElement('span', { style: { width:6,height:6,borderRadius:'50%',background:'#48BFA0' } }),
          'Validé'
        ),
        !link.validated && React.createElement('span', {
          style: { display:'inline-flex',alignItems:'center',gap:6,fontSize:12,color:'#F4A236',fontWeight:500 }
        },
          React.createElement('span', { style: { width:6,height:6,borderRadius:'50%',background:'#F4A236' } }),
          'En attente de validation'
        ),
      ),
      React.createElement('h1', {
        style: { fontSize:30,fontWeight:600,color:'var(--ink)',letterSpacing:'-0.02em',marginBottom:6 }
      }, 'Parrainage'),
      React.createElement('p', { style: { fontSize:14,color:'var(--ink-3)',marginBottom:24 } },
        `Conclu le ${dateStr}`),

      /* Two persons card */
      React.createElement('div', {
        style: { background:'var(--surface)',border:'1px solid var(--line)',borderRadius:16,padding:32,marginBottom:20,
          position:'relative',overflow:'hidden' }
      },
        React.createElement('div', {
          style: { position:'absolute',inset:0,
            background:`linear-gradient(135deg, ${father.color}10 0%, transparent 50%, ${child.color}10 100%)`,
            pointerEvents:'none' }
        }),
        React.createElement('div', {
          style: { display:'flex',alignItems:'center',gap:16,flexWrap:'wrap',justifyContent:'center',position:'relative' }
        },
          /* Father */
          React.createElement('button', {
            onClick: () => navigate('profile', { id: father.id }),
            style: { display:'flex',flexDirection:'column',alignItems:'center',gap:10,padding:'18px 24px',
              border:'none',background:'transparent',cursor:'pointer',fontFamily:'inherit',flex:'1 1 200px' }
          },
            React.createElement('div', {
              style: { borderRadius:'50%',overflow:'hidden',border:`3px solid ${father.color}`,
                boxShadow:`0 8px 24px ${father.color}30` }
            },
              React.createElement(Avatar, { person: father, size: 96 })
            ),
            React.createElement('div', { style: { fontSize:11,color:'var(--ink-3)',textTransform:'uppercase',letterSpacing:'0.08em',fontWeight:600,marginTop:4 } }, 'Parrain·marraine'),
            React.createElement('div', { style: { fontSize:18,fontWeight:600,color:'var(--ink)',letterSpacing:'-0.01em' } },
              father.firstName + ' ' + father.lastName),
            React.createElement('div', { style: { fontSize:12,color:father.color,fontWeight:500 } }, `Promo ${father.startYear}`),
          ),

          /* Connector */
          React.createElement('div', {
            style: { display:'flex',flexDirection:'column',alignItems:'center',gap:6,minWidth:80 }
          },
            React.createElement('div', { style: { fontSize:18,color:meta.color } }, meta.icon),
            React.createElement('div', {
              style: { width:60,height:2,background:`linear-gradient(to right, ${father.color}, ${child.color})`,borderRadius:1 }
            }),
            React.createElement('div', { style: { fontSize:11,color:'var(--ink-3)',textTransform:'uppercase',letterSpacing:'0.06em',fontWeight:500 } }, 'parraine'),
          ),

          /* Child */
          React.createElement('button', {
            onClick: () => navigate('profile', { id: child.id }),
            style: { display:'flex',flexDirection:'column',alignItems:'center',gap:10,padding:'18px 24px',
              border:'none',background:'transparent',cursor:'pointer',fontFamily:'inherit',flex:'1 1 200px' }
          },
            React.createElement('div', {
              style: { borderRadius:'50%',overflow:'hidden',border:`3px solid ${child.color}`,
                boxShadow:`0 8px 24px ${child.color}30` }
            },
              React.createElement(Avatar, { person: child, size: 96 })
            ),
            React.createElement('div', { style: { fontSize:11,color:'var(--ink-3)',textTransform:'uppercase',letterSpacing:'0.08em',fontWeight:600,marginTop:4 } }, 'Fillot·e'),
            React.createElement('div', { style: { fontSize:18,fontWeight:600,color:'var(--ink)',letterSpacing:'-0.01em' } },
              child.firstName + ' ' + child.lastName),
            React.createElement('div', { style: { fontSize:12,color:child.color,fontWeight:500 } }, `Promo ${child.startYear}`),
          ),
        )
      ),

      /* Details */
      React.createElement('div', {
        style: { display:'grid',gridTemplateColumns:'repeat(auto-fit,minmax(220px,1fr))',gap:12,marginBottom:20 }
      },
        [
          { l:'Date', v: dateStr },
          { l:'Type', v: meta.label, accent: meta.color },
          { l:'Statut', v: link.validated ? 'Validé' : 'En attente' },
        ].map(s =>
          React.createElement('div', {
            key: s.l,
            style: { background:'var(--surface)',border:'1px solid var(--line)',borderRadius:12,padding:'16px 18px' }
          },
            React.createElement('div', {
              style: { fontSize:11,color:'var(--ink-3)',textTransform:'uppercase',letterSpacing:'0.06em',fontWeight:500 }
            }, s.l),
            React.createElement('div', {
              style: { fontSize:18,fontWeight:600,marginTop:6,letterSpacing:'-0.01em',
                color: s.accent || 'var(--ink)' }
            }, s.v),
          )
        )
      ),

      /* Type description */
      React.createElement('div', {
        style: { background:'var(--surface)',border:'1px solid var(--line)',borderRadius:14,padding:24,marginBottom:20,
          display:'flex',gap:16,alignItems:'flex-start' }
      },
        React.createElement('div', {
          style: { width:42,height:42,borderRadius:11,background:`${meta.color}15`,color:meta.color,
            display:'flex',alignItems:'center',justifyContent:'center',fontSize:18,flexShrink:0 }
        }, meta.icon),
        React.createElement('div', null,
          React.createElement('div', { style: { fontSize:14,fontWeight:600,color:'var(--ink)',marginBottom:4 } },
            `Type : ${meta.label}`),
          React.createElement('div', { style: { fontSize:13.5,color:'var(--ink-2)',lineHeight:1.55 } }, meta.desc),
        )
      ),

      /* Reason */
      React.createElement('div', {
        style: { background:'var(--surface)',border:'1px solid var(--line)',borderRadius:14,padding:24,marginBottom:20 }
      },
        React.createElement('div', {
          style: { fontSize:11,color:'var(--ink-3)',textTransform:'uppercase',letterSpacing:'0.06em',fontWeight:500,marginBottom:10 }
        }, 'Raison du parrainage'),
        link.reason ? React.createElement('p', {
          style: { fontSize:15,color:'var(--ink)',lineHeight:1.65,fontStyle:'italic' }
        }, '« ' + link.reason + ' »')
        : React.createElement('p', {
          style: { fontSize:13.5,color:'var(--ink-4)',fontStyle:'italic' }
        }, 'Aucune raison renseignée pour ce parrainage.')
      ),

      /* Actions */
      React.createElement('div', { style: { display:'flex',gap:10,flexWrap:'wrap' } },
        React.createElement('button', {
          onClick: () => navigate('link', { fromId: father.id, toId: child.id }),
          style: { padding:'10px 16px',border:'1px solid var(--line)',borderRadius:9,background:'var(--surface)',
            color:'var(--ink)',fontSize:13,fontWeight:500,cursor:'pointer',fontFamily:'inherit' }
        }, 'Voir dans l\'arbre'),
        React.createElement('button', {
          style: { padding:'10px 16px',border:'1px solid var(--line)',borderRadius:9,background:'transparent',
            color:'var(--ink-2)',fontSize:13,fontWeight:500,cursor:'pointer',fontFamily:'inherit' }
        }, 'Modifier'),
      )
    )
  );
}

/* ============================================================
 * App + Router
 * ============================================================ */
function App() {
  const [t, setTweak] = useTweaks(TWEAK_DEFAULTS);
  const [route, setRoute] = useState({ page: 'home', params: {} });

  // Apply theme
  useEffect(() => {
    document.body.dataset.theme = t.theme;
  }, [t.theme]);

  const navigate = (page, params = {}) => {
    setRoute({ page, params });
    window.scrollTo({ top: 0, behavior: 'instant' });
  };

  const renderPage = () => {
    switch (route.page) {
      case 'home':      return React.createElement(HomePage, { navigate });
      case 'directory': return React.createElement(DirectoryPage, { navigate, t, setTweak });
      case 'profile':   return React.createElement(ProfilePage, { navigate, personId: route.params.id || 4 });
      case 'link':      return React.createElement(LinkPage, { navigate,
                          initialFromId: route.params.fromId, initialToId: route.params.toId });
      case 'sponsorship': return React.createElement(SponsorshipPage, { navigate,
                          fromId: route.params.fromId, toId: route.params.toId });
      case 'about':     return React.createElement('div', {
        style: { padding:60,textAlign:'center',color:'var(--ink-3)',background:'var(--bg)',minHeight:'calc(100vh - 60px)' }
      }, 'Page « À propos » — non incluse dans cette maquette.');
      default:          return React.createElement(HomePage, { navigate });
    }
  };

  return React.createElement(React.Fragment, null,
    React.createElement(Header, { currentPage: route.page, navigate }),
    renderPage(),

    React.createElement(TweaksPanel, null,
      React.createElement(TweakSection, { title: 'Apparence' },
        React.createElement(TweakRadio, { label:'Thème', value:t.theme, options:['light','dark'],
          labels:['Clair','Sombre'], onChange:v=>setTweak('theme',v) }),
      ),
      route.page === 'directory' && React.createElement(TweakSection, { title: 'Annuaire — Vue' },
        React.createElement(TweakSelect, { label:'Mode', value:t.directoryView,
          options:['grid','list','timeline','tree'], labels:['Grille','Liste','Timeline','Arbre'],
          onChange:v=>setTweak('directoryView',v) }),
      ),
      route.page === 'directory' && t.directoryView === 'grid' && React.createElement(TweakSection, { title: 'Style des cartes' },
        React.createElement(TweakRadio, { label:'Style', value:t.cardStyle, options:['modern','compact'],
          labels:['Moderne','Compact'], onChange:v=>setTweak('cardStyle',v) }),
        React.createElement(TweakToggle, { label:'Barre couleur promo', value:t.showPromoBar, onChange:v=>setTweak('showPromoBar',v) })
      ),
      route.page === 'directory' && t.directoryView === 'tree' && React.createElement(TweakSection, { title: 'Vue arbre' },
        React.createElement(TweakRadio, { label:'Filtre', value:t.treeFilterMode, options:['all','connected','isolated'],
          labels:['Tous','Liens','Isolés'], onChange:v=>setTweak('treeFilterMode',v) }),
        React.createElement(TweakToggle, { label:'Afficher les noms', value:t.treeShowLabels, onChange:v=>setTweak('treeShowLabels',v) })
      ),
      React.createElement(TweakSection, { title: 'Démo' },
        React.createElement(TweakButton, { label: '→ Profil exemple', onClick: () => navigate('profile', { id: 4 }) }),
        React.createElement(TweakButton, { label: '→ Lien Henri ↔ Léa', onClick: () => navigate('link', { fromId: 1, toId: 9 }) }),
        React.createElement(TweakButton, { label: '→ Parrainage exemple', onClick: () => { const l = LINKS[3]; navigate('sponsorship', { fromId: l.godFatherId, toId: l.godChildId }); } }),
      )
    )
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(React.createElement(App));
