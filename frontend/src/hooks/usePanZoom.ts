import { useCallback, useEffect, useRef, useState } from 'react';

/**
 * Pan & zoom d'un canvas transformé (`translate(x, y) scale(k)`, origine 0 0).
 *
 * - Pointer Events : souris, stylet et tactile (glisser à un doigt, pincer à deux).
 * - Zoom ancré sur le curseur / le centre du pincement / le centre du conteneur.
 * - Molette : `wheel: 'zoom'` zoome toujours ; `wheel: 'modifier'` laisse défiler la page
 *   et ne zoome qu'avec Ctrl/⌘ (le pincement trackpad émet ctrlKey) — utile quand le
 *   canvas est au milieu d'une page qui défile.
 * - `fitTo(bounds)` cadre un rectangle du canvas dans le conteneur.
 *
 * Les nœuds restent cliquables : un drag ne démarre qu'au-delà d'un seuil, et `didDrag`
 * permet aux onClick d'ignorer le clic qui termine un glisser.
 */

export interface Transform {
  x: number;
  y: number;
  k: number;
}

export interface Bounds {
  x: number;
  y: number;
  width: number;
  height: number;
}

interface UsePanZoomOptions {
  minZoom?: number;
  maxZoom?: number;
  wheel?: 'zoom' | 'modifier';
}

export interface UsePanZoomResult {
  transform: Transform;
  zoom: number;
  isDragging: boolean;
  /** Vrai si le dernier geste était un glisser (à tester dans les onClick) */
  didDrag: boolean;
  /** Vrai brièvement quand l'utilisateur fait défiler sans modificateur (mode 'modifier') */
  wheelHint: boolean;
  containerRef: (el: HTMLDivElement | null) => void;
  /** Zoome d'un facteur autour du centre du conteneur */
  zoomBy: (factor: number) => void;
  /** Cadre le rectangle donné (coordonnées canvas) dans le conteneur */
  fitTo: (bounds: Bounds, options?: { padding?: number; maxZoom?: number }) => void;
  setTransform: (t: Transform) => void;
}

const DRAG_THRESHOLD = 5;
const IGNORE_SELECTOR = 'button, input, select, textarea, a, [data-pz-ignore]';

export function transformStyle(t: Transform): string {
  return `translate(${String(t.x)}px, ${String(t.y)}px) scale(${String(t.k)})`;
}

export function usePanZoom({
  minZoom = 0.2,
  maxZoom = 2.5,
  wheel = 'zoom',
}: UsePanZoomOptions = {}): UsePanZoomResult {
  const [transform, setTransformState] = useState<Transform>({ x: 0, y: 0, k: 1 });
  const [isDragging, setIsDragging] = useState(false);
  const [wheelHint, setWheelHint] = useState(false);

  const tRef = useRef(transform);
  const elRef = useRef<HTMLDivElement | null>(null);
  const didDragRef = useRef(false);
  const optsRef = useRef({ minZoom, maxZoom, wheel });
  const hintTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    optsRef.current = { minZoom, maxZoom, wheel };
  }, [minZoom, maxZoom, wheel]);

  useEffect(
    () => () => {
      if (hintTimer.current) clearTimeout(hintTimer.current);
    },
    [],
  );

  const setTransform = useCallback((t: Transform) => {
    tRef.current = t;
    setTransformState(t);
  }, []);

  const clampK = (k: number) =>
    Math.max(optsRef.current.minZoom, Math.min(optsRef.current.maxZoom, k));

  /** Zoom vers `k` en gardant le point (px, py) du conteneur immobile, depuis `from` */
  const zoomAround = useCallback(
    (from: Transform, k: number, px: number, py: number): Transform => {
      const nk = clampK(k);
      const ratio = nk / from.k;
      return { x: px - (px - from.x) * ratio, y: py - (py - from.y) * ratio, k: nk };
    },
    [],
  );

  const zoomBy = useCallback(
    (factor: number) => {
      const el = elRef.current;
      const cx = el ? el.clientWidth / 2 : 0;
      const cy = el ? el.clientHeight / 2 : 0;
      setTransform(zoomAround(tRef.current, tRef.current.k * factor, cx, cy));
    },
    [setTransform, zoomAround],
  );

  const fitTo = useCallback(
    (bounds: Bounds, options: { padding?: number; maxZoom?: number } = {}) => {
      const el = elRef.current;
      if (!el || bounds.width <= 0 || bounds.height <= 0) return;
      const padding = options.padding ?? 32;
      const cw = el.clientWidth;
      const ch = el.clientHeight;
      const k = Math.max(
        optsRef.current.minZoom,
        Math.min(
          options.maxZoom ?? 1,
          (cw - padding * 2) / bounds.width,
          (ch - padding * 2) / bounds.height,
        ),
      );
      setTransform({
        x: (cw - bounds.width * k) / 2 - bounds.x * k,
        y: (ch - bounds.height * k) / 2 - bounds.y * k,
        k,
      });
    },
    [setTransform],
  );

  const containerRef = useCallback(
    (el: HTMLDivElement | null) => {
      elRef.current = el;
      if (!el) return;

      const pointers = new Map<number, { x: number; y: number }>();
      let drag: { id: number; sx: number; sy: number; from: Transform; active: boolean } | null =
        null;
      let pinch: {
        d0: number;
        mx0: number;
        my0: number;
        from: Transform;
      } | null = null;

      const local = (e: PointerEvent | WheelEvent) => {
        const r = el.getBoundingClientRect();
        return { x: e.clientX - r.left, y: e.clientY - r.top };
      };

      const startPinch = () => {
        const [a, b] = [...pointers.values()];
        if (!a || !b) return;
        pinch = {
          d0: Math.hypot(b.x - a.x, b.y - a.y) || 1,
          mx0: (a.x + b.x) / 2,
          my0: (a.y + b.y) / 2,
          from: tRef.current,
        };
        drag = null;
        didDragRef.current = true;
      };

      const onWheel = (e: WheelEvent) => {
        const withModifier = e.ctrlKey || e.metaKey;
        if (optsRef.current.wheel === 'modifier' && !withModifier) {
          setWheelHint(true);
          if (hintTimer.current) clearTimeout(hintTimer.current);
          hintTimer.current = setTimeout(() => {
            setWheelHint(false);
          }, 1400);
          return; // laisse la page défiler
        }
        e.preventDefault();
        const unit = e.deltaMode === 1 ? 16 : e.deltaMode === 2 ? 400 : 1;
        // Pincement trackpad (ctrlKey) : deltas faibles → plus sensible
        const speed = e.ctrlKey ? 0.01 : 0.0015;
        const factor = Math.exp(-e.deltaY * unit * speed);
        const p = local(e);
        setTransform(zoomAround(tRef.current, tRef.current.k * factor, p.x, p.y));
      };

      const onPointerDown = (e: PointerEvent) => {
        if (e.pointerType === 'mouse' && e.button !== 0) return;
        if ((e.target as Element).closest(IGNORE_SELECTOR)) return;
        const p = local(e);
        pointers.set(e.pointerId, p);
        if (pointers.size === 1) {
          didDragRef.current = false;
          drag = { id: e.pointerId, sx: p.x, sy: p.y, from: tRef.current, active: false };
        } else if (pointers.size === 2) {
          el.setPointerCapture(e.pointerId);
          startPinch();
          setIsDragging(true);
        }
      };

      const onPointerMove = (e: PointerEvent) => {
        if (!pointers.has(e.pointerId)) return;
        const p = local(e);
        pointers.set(e.pointerId, p);

        if (pinch && pointers.size >= 2) {
          const [a, b] = [...pointers.values()];
          if (!a || !b) return;
          const d = Math.hypot(b.x - a.x, b.y - a.y);
          const mx = (a.x + b.x) / 2;
          const my = (a.y + b.y) / 2;
          const z = zoomAround(pinch.from, pinch.from.k * (d / pinch.d0), pinch.mx0, pinch.my0);
          setTransform({ ...z, x: z.x + (mx - pinch.mx0), y: z.y + (my - pinch.my0) });
          return;
        }

        if (drag?.id !== e.pointerId) return;
        const dx = p.x - drag.sx;
        const dy = p.y - drag.sy;
        if (!drag.active) {
          if (Math.hypot(dx, dy) < DRAG_THRESHOLD) return;
          drag.active = true;
          didDragRef.current = true;
          // Capture seulement une fois le drag avéré : sinon le clic sur un nœud serait
          // redirigé vers le conteneur.
          el.setPointerCapture(e.pointerId);
          setIsDragging(true);
        }
        setTransform({ ...drag.from, x: drag.from.x + dx, y: drag.from.y + dy });
      };

      const onPointerUp = (e: PointerEvent) => {
        if (!pointers.delete(e.pointerId)) return;
        if (el.hasPointerCapture(e.pointerId)) el.releasePointerCapture(e.pointerId);
        if (pinch && pointers.size < 2) {
          pinch = null;
          // Le doigt restant peut continuer à faire glisser
          const [rest] = [...pointers.entries()];
          drag = rest
            ? { id: rest[0], sx: rest[1].x, sy: rest[1].y, from: tRef.current, active: true }
            : null;
        } else if (drag?.id === e.pointerId) {
          drag = null;
        }
        if (pointers.size === 0) setIsDragging(false);
      };

      el.addEventListener('wheel', onWheel, { passive: false });
      el.addEventListener('pointerdown', onPointerDown);
      el.addEventListener('pointermove', onPointerMove);
      el.addEventListener('pointerup', onPointerUp);
      el.addEventListener('pointercancel', onPointerUp);
      return () => {
        el.removeEventListener('wheel', onWheel);
        el.removeEventListener('pointerdown', onPointerDown);
        el.removeEventListener('pointermove', onPointerMove);
        el.removeEventListener('pointerup', onPointerUp);
        el.removeEventListener('pointercancel', onPointerUp);
      };
    },
    [setTransform, zoomAround],
  );

  return {
    transform,
    zoom: transform.k,
    isDragging,
    get didDrag() {
      return didDragRef.current;
    },
    wheelHint,
    containerRef,
    zoomBy,
    fitTo,
    setTransform,
  };
}
