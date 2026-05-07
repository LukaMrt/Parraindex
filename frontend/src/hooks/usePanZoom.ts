import { useCallback, useRef, useState } from 'react';
import type { Dispatch, MouseEvent, SetStateAction } from 'react';

interface UsePanZoomOptions {
  /** Sélecteur CSS d'un élément enfant qui bloque le drag quand cliqué */
  dragBlockSelector?: string;
  minZoom?: number;
  maxZoom?: number;
}

export interface UsePanZoomResult {
  pan: { x: number; y: number };
  zoom: number;
  isDragging: boolean;
  /** Indique si un drag a eu lieu depuis le dernier mousedown (pour distinguer click vs drag) */
  didDrag: boolean;
  containerRef: (el: HTMLDivElement | null) => void;
  setPan: Dispatch<SetStateAction<{ x: number; y: number }>>;
  setZoom: Dispatch<SetStateAction<number>>;
  handleMouseDown: (e: MouseEvent) => void;
  handleMouseMove: (e: MouseEvent) => void;
  handleMouseUp: () => void;
  resetView: () => void;
}

export function usePanZoom({
  dragBlockSelector,
  minZoom = 0.3,
  maxZoom = 2.5,
}: UsePanZoomOptions = {}): UsePanZoomResult {
  const [pan, setPan] = useState({ x: 0, y: 0 });
  const [zoom, setZoom] = useState(1);
  const [isDragging, setIsDragging] = useState(false);
  const dragRef = useRef({ active: false, startX: 0, startY: 0, originX: 0, originY: 0 });
  const didDragRef = useRef(false);

  // Callback ref : s'exécute dès que l'élément est monté ou démonté
  const containerRef = useCallback(
    (el: HTMLDivElement | null) => {
      if (!el) return;
      const onWheel = (e: WheelEvent) => {
        e.preventDefault();
        setZoom((z) => Math.max(minZoom, Math.min(maxZoom, e.deltaY < 0 ? z * 1.1 : z / 1.1)));
      };
      el.addEventListener('wheel', onWheel, { passive: false });
      return () => { el.removeEventListener('wheel', onWheel); };
    },
    [minZoom, maxZoom],
  );

  const handleMouseDown = (e: MouseEvent) => {
    if (dragBlockSelector && (e.target as HTMLElement).closest(dragBlockSelector)) return;
    didDragRef.current = false;
    dragRef.current = {
      active: true,
      startX: e.clientX,
      startY: e.clientY,
      originX: pan.x,
      originY: pan.y,
    };
  };

  const handleMouseMove = (e: MouseEvent) => {
    if (!dragRef.current.active) return;
    didDragRef.current = true;
    if (!isDragging) setIsDragging(true);
    setPan({
      x: dragRef.current.originX + (e.clientX - dragRef.current.startX),
      y: dragRef.current.originY + (e.clientY - dragRef.current.startY),
    });
  };

  const handleMouseUp = () => {
    dragRef.current.active = false;
    setIsDragging(false);
  };

  const resetView = () => {
    setZoom(1);
    setPan({ x: 0, y: 0 });
  };

  return {
    pan,
    zoom,
    isDragging,
    get didDrag() { return didDragRef.current; },
    containerRef,
    setPan,
    setZoom,
    handleMouseDown,
    handleMouseMove,
    handleMouseUp,
    resetView,
  };
}
