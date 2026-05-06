/* ============================================================
 * FamilyGraph — interactive mini-tree for the ProfilePage.
 * Same vocabulary as the directory TreeView: pan, zoom, edges
 * drawn as cubic Béziers, indirect (transitive) links dashed.
 * ============================================================ */
const { ALL_PERSONS: FG_PERSONS, LINKS: FG_LINKS, PROMO_COLORS: FG_PROMO_COLORS, getGodFathers: fgGetFathers, getGodChildren: fgGetChildren } = window.PARRAINDEX;

function FamilyGraph({ person, ancestorDepth, descendantDepth, navigate }) {
  const containerRef = React.useRef(null);
  const [pan, setPan] = React.useState({ x: 0, y: 0 });
  const [zoom, setZoom] = React.useState(1);
  const [hoverId, setHoverId] = React.useState(null);
  const dragRef = React.useRef({ active: false, moved: false, startX: 0, startY: 0, originX: 0, originY: 0 });

  // ── Compute generations + positions
  const layout = React.useMemo(() => {
    // Walk up
    const ancestorGens = [];
    {
      let frontier = [person.id];
      const seen = new Set([person.id]);
      for (let d = 0; d < ancestorDepth; d++) {
        const next = [];
        const nextIds = new Set();
        frontier.forEach(id => fgGetFathers(id).forEach(f => {
          if (!seen.has(f.id) && !nextIds.has(f.id)) { nextIds.add(f.id); next.push(f); }
        }));
        if (!next.length) break;
        next.forEach(p => seen.add(p.id));
        ancestorGens.push(next);
        frontier = next.map(p => p.id);
      }
    }
    // Walk down
    const descendantGens = [];
    {
      let frontier = [person.id];
      const seen = new Set([person.id]);
      for (let d = 0; d < descendantDepth; d++) {
        const next = [];
        const nextIds = new Set();
        frontier.forEach(id => fgGetChildren(id).forEach(c => {
          if (!seen.has(c.id) && !nextIds.has(c.id)) { nextIds.add(c.id); next.push(c); }
        }));
        if (!next.length) break;
        next.forEach(p => seen.add(p.id));
        descendantGens.push(next);
        frontier = next.map(p => p.id);
      }
    }

    // Stack rows top→bottom: oldest ancestors → person → descendants
    const rows = [
      ...[...ancestorGens].reverse(),  // oldest first
      [person],                         // self row
      ...descendantGens,
    ];
    const selfRowIdx = ancestorGens.length;

    // Position: COL_W centered around 0, ROW_H steps
    const COL_W = 110;
    const ROW_H = 110;
    const positions = {};
    let maxCols = 1;
    rows.forEach((row, rIdx) => {
      maxCols = Math.max(maxCols, row.length);
      const totalW = (row.length - 1) * COL_W;
      row.forEach((p, cIdx) => {
        positions[p.id] = {
          x: cIdx * COL_W - totalW / 2,
          y: rIdx * ROW_H,
          row: rIdx,
        };
      });
    });

    // Build set of visible ids
    const visibleIds = new Set();
    rows.forEach(r => r.forEach(p => visibleIds.add(p.id)));

    // Direct links among visible nodes
    const directLinks = FG_LINKS.filter(l => visibleIds.has(l.godFatherId) && visibleIds.has(l.godChildId));
    const directKey = new Set(directLinks.map(l => `${l.godFatherId}-${l.godChildId}`));

    // Indirect links: BFS from each visible node through hidden descendants
    const childrenOf = {};
    FG_LINKS.forEach(l => {
      if (!childrenOf[l.godFatherId]) childrenOf[l.godFatherId] = [];
      childrenOf[l.godFatherId].push(l.godChildId);
    });
    const indirectLinks = [];
    const indirectKey = new Set();
    visibleIds.forEach(srcId => {
      const stack = [{ id: srcId, hops: 0 }];
      const seen = new Set([srcId]);
      while (stack.length) {
        const { id, hops } = stack.pop();
        const kids = childrenOf[id] || [];
        for (const kid of kids) {
          if (seen.has(kid)) continue;
          seen.add(kid);
          if (visibleIds.has(kid)) {
            if (hops >= 1) {
              const k = `${srcId}-${kid}`;
              if (!directKey.has(k) && !indirectKey.has(k)) {
                indirectKey.add(k);
                indirectLinks.push({ godFatherId: srcId, godChildId: kid });
              }
            }
          } else {
            stack.push({ id: kid, hops: hops + 1 });
          }
        }
      }
    });

    const allNodes = [];
    rows.forEach(r => r.forEach(p => allNodes.push(p)));

    return {
      rows, selfRowIdx, positions, allNodes,
      directLinks, indirectLinks,
      width: maxCols * COL_W + 80,
      height: rows.length * ROW_H + 60,
    };
  }, [person.id, ancestorDepth, descendantDepth]);

  const handleMouseDown = (e) => {
    if (e.target.closest('[data-fg-node]') || e.target.closest('button')) return;
    dragRef.current = {
      active: true, moved: false,
      startX: e.clientX, startY: e.clientY,
      originX: pan.x, originY: pan.y,
    };
  };
  const handleMouseMove = (e) => {
    if (!dragRef.current.active) return;
    const dx = e.clientX - dragRef.current.startX;
    const dy = e.clientY - dragRef.current.startY;
    if (Math.abs(dx) + Math.abs(dy) > 3) dragRef.current.moved = true;
    setPan({ x: dragRef.current.originX + dx, y: dragRef.current.originY + dy });
  };
  const handleMouseUp = () => { dragRef.current.active = false; };
  const handleWheel = (e) => {
    e.preventDefault();
    const factor = e.deltaY < 0 ? 1.1 : 0.9;
    setZoom(z => Math.max(0.4, Math.min(2.5, z * factor)));
  };
  const recenter = () => { setPan({ x: 0, y: 0 }); setZoom(1); };

  // Container size
  const HEIGHT = Math.max(360, Math.min(620, 80 + layout.rows.length * 110));

  // Highlight neighbors of hovered node
  const highlightedIds = React.useMemo(() => {
    if (!hoverId) return null;
    const set = new Set([hoverId]);
    FG_LINKS.forEach(l => {
      if (l.godFatherId === hoverId) set.add(l.godChildId);
      if (l.godChildId === hoverId) set.add(l.godFatherId);
    });
    return set;
  }, [hoverId]);

  return React.createElement('div', {
    ref: containerRef,
    onMouseDown: handleMouseDown,
    onMouseMove: handleMouseMove,
    onMouseUp: handleMouseUp,
    onMouseLeave: handleMouseUp,
    onWheel: handleWheel,
    style: {
      position:'relative', width:'100%', height: HEIGHT,
      background:'var(--bg)', border:'1px solid var(--line)', borderRadius:14, overflow:'hidden',
      cursor: dragRef.current.active ? 'grabbing' : 'grab',
      backgroundImage:'radial-gradient(circle, var(--line) 1px, transparent 1px)',
      backgroundSize:'22px 22px',
      userSelect:'none',
    }
  },
    /* Zoom controls */
    React.createElement('div', {
      style: { position:'absolute', top:10, right:10, display:'flex', flexDirection:'column', gap:6, zIndex:10 }
    },
      ['+','−','⤓'].map((sym, i) => React.createElement('button', {
        key: sym,
        onClick: (e) => {
          e.stopPropagation();
          if (i===0) setZoom(z => Math.min(2.5, z*1.2));
          else if (i===1) setZoom(z => Math.max(0.4, z/1.2));
          else recenter();
        },
        style: { width:30, height:30, border:'1px solid var(--line)', borderRadius:8,
          background:'var(--surface)', cursor:'pointer', fontSize:13, fontWeight:600,
          color:'var(--ink-2)', fontFamily:'inherit' }
      }, sym))
    ),

    /* Legend */
    React.createElement('div', {
      style: { position:'absolute', bottom:10, left:10, zIndex:10, fontSize:11.5,
        color:'var(--ink-3)', background:'var(--surface)', border:'1px solid var(--line)',
        padding:'5px 10px', borderRadius:7,
        display:'flex', gap:12, alignItems:'center' }
    },
      React.createElement('span', null, `${layout.allNodes.length} personnes · ${layout.directLinks.length} liens${layout.indirectLinks.length ? ` · ${layout.indirectLinks.length} indirects` : ''}`),
      React.createElement('span', { style:{ color:'var(--ink-4)' } }, `${Math.round(zoom*100)}%`)
    ),

    /* Inner transformed canvas */
    React.createElement('div', {
      style: {
        position:'absolute',
        left:'50%', top: 30,
        transform:`translate(calc(-50% + ${pan.x}px), ${pan.y}px) scale(${zoom})`,
        transformOrigin:'50% 0',
        width: layout.width, height: layout.height,
        transition: dragRef.current.active ? 'none' : 'transform 0.12s ease',
      }
    },
      /* Edges */
      React.createElement('svg', {
        style:{ position:'absolute', inset:0, width:'100%', height:'100%', pointerEvents:'none', overflow:'visible' }
      },
        /* Indirect first (under) */
        layout.indirectLinks.map((l, i) => {
          const a = layout.positions[l.godFatherId];
          const b = layout.positions[l.godChildId];
          if (!a || !b) return null;
          const ax = a.x + layout.width/2, ay = a.y + 28;
          const bx = b.x + layout.width/2, by = b.y + 28;
          const cy = (ay + by) / 2;
          const path = `M${ax},${ay} C${ax},${cy} ${bx},${cy} ${bx},${by}`;
          const c = FG_PROMO_COLORS[FG_PERSONS.find(p => p.id === l.godFatherId)?.startYear] || '#999';
          const dim = highlightedIds && !highlightedIds.has(l.godFatherId) && !highlightedIds.has(l.godChildId);
          return React.createElement('path', {
            key:`i-${i}`, d:path, stroke:c, strokeWidth:1.25, fill:'none',
            strokeDasharray:'3 4', opacity: dim ? 0.12 : 0.5, strokeLinecap:'round',
          });
        }),
        /* Direct */
        layout.directLinks.map((l, i) => {
          const a = layout.positions[l.godFatherId];
          const b = layout.positions[l.godChildId];
          if (!a || !b) return null;
          const ax = a.x + layout.width/2, ay = a.y + 28;
          const bx = b.x + layout.width/2, by = b.y + 28;
          const cy = (ay + by) / 2;
          const path = `M${ax},${ay} C${ax},${cy} ${bx},${cy} ${bx},${by}`;
          const c = FG_PROMO_COLORS[FG_PERSONS.find(p => p.id === l.godFatherId)?.startYear] || '#999';
          const isHighlighted = highlightedIds && (highlightedIds.has(l.godFatherId) && highlightedIds.has(l.godChildId));
          const dim = highlightedIds && !isHighlighted;
          return React.createElement('path', {
            key:`d-${i}`, d:path, stroke:c,
            strokeWidth: isHighlighted ? 2.5 : 1.5,
            fill:'none',
            opacity: dim ? 0.12 : (isHighlighted ? 0.95 : 0.55),
          });
        })
      ),

      /* Nodes */
      layout.allNodes.map(p => {
        const pos = layout.positions[p.id];
        if (!pos) return null;
        const isSelf = p.id === person.id;
        const dim = highlightedIds && !highlightedIds.has(p.id);
        return React.createElement('div', {
          key: p.id,
          'data-fg-node': true,
          onMouseEnter: () => setHoverId(p.id),
          onMouseLeave: () => setHoverId(null),
          onClick: (e) => {
            e.stopPropagation();
            if (dragRef.current.moved) return;
            if (!isSelf) navigate('profile', { id: p.id });
          },
          style: {
            position:'absolute',
            left: pos.x + layout.width/2 - 30,
            top: pos.y,
            width: 60,
            display:'flex', flexDirection:'column', alignItems:'center', gap:4,
            cursor: isSelf ? 'default' : 'pointer',
            opacity: dim ? 0.35 : 1,
            transition: 'opacity 0.15s, transform 0.15s',
          },
        },
          React.createElement('div', {
            style: {
              width: isSelf ? 56 : 44, height: isSelf ? 56 : 44,
              borderRadius:'50%', overflow:'hidden',
              border:`${isSelf ? 3 : 2}px solid ${p.color}`,
              boxShadow: isSelf
                ? `0 0 0 4px ${p.color}25, 0 8px 18px ${p.color}30`
                : '0 2px 6px rgba(0,0,0,0.06)',
              background:'#fff',
            }
          },
            React.createElement(window.UI.Avatar, { person: p, size: isSelf ? 56 : 44 })
          ),
          React.createElement('div', {
            style: { fontSize:10.5, fontWeight: isSelf ? 600 : 500,
              color: isSelf ? 'var(--ink)' : 'var(--ink-2)',
              textAlign:'center', lineHeight:1.15,
              maxWidth: 72, whiteSpace:'nowrap', overflow:'hidden', textOverflow:'ellipsis',
              background:'var(--surface)', padding:'2px 5px', borderRadius:4,
              border:'1px solid var(--line)',
            }
          }, p.firstName)
        );
      })
    )
  );
}

window.FamilyGraph = FamilyGraph;
