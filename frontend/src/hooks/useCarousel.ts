import { useCallback, useRef, useState } from 'react';
import type { MouseEvent, RefObject } from 'react';

export interface CarouselControls {
  sliderRef: RefObject<HTMLDivElement | null>;
  dragStartX: { current: number };
  scrollProgress: number;
  centeredIndex: number;
  onScroll: () => void;
  onProgressChange: (value: number) => void;
  onMouseDown: (e: MouseEvent) => void;
  onMouseMove: (e: MouseEvent) => void;
  onMouseUp: () => void;
}

export function useCarousel(itemCount: number): CarouselControls {
  const sliderRef = useRef<HTMLDivElement>(null);
  const [scrollProgress, setScrollProgress] = useState(0.5);
  const [centeredIndex, setCenteredIndex] = useState(0);

  const dragStartX = useRef<number>(0);
  const dragScrollLeft = useRef<number>(0);
  const isDragging = useRef<boolean>(false);

  const readProgress = useCallback((): number => {
    const el = sliderRef.current;
    if (el === null) return 0;
    const max = el.scrollWidth - el.clientWidth;
    return max === 0 ? 0 : el.scrollLeft / max;
  }, []);

  const applyProgress = useCallback(
    (progress: number) => {
      setScrollProgress(progress);
      setCenteredIndex(Math.round(progress * Math.max(0, itemCount - 1)));
    },
    [itemCount],
  );

  const onScroll = useCallback(() => {
    applyProgress(readProgress());
  }, [applyProgress, readProgress]);

  const onProgressChange = useCallback(
    (value: number) => {
      const el = sliderRef.current;
      if (el === null) return;
      el.scrollLeft = value * (el.scrollWidth - el.clientWidth);
      applyProgress(value);
    },
    [applyProgress],
  );

  const onMouseDown = useCallback((e: MouseEvent) => {
    const el = sliderRef.current;
    if (el === null) return;
    isDragging.current = true;
    dragStartX.current = e.pageX - el.offsetLeft;
    dragScrollLeft.current = el.scrollLeft;
  }, []);

  const onMouseMove = useCallback((e: MouseEvent) => {
    if (!isDragging.current) return;
    const el = sliderRef.current;
    if (el === null) return;
    const x = e.pageX - el.offsetLeft;
    el.scrollLeft = dragScrollLeft.current - (x - dragStartX.current);
  }, []);

  const onMouseUp = useCallback(() => {
    isDragging.current = false;
  }, []);

  return {
    sliderRef,
    dragStartX,
    scrollProgress,
    centeredIndex,
    onScroll,
    onProgressChange,
    onMouseDown,
    onMouseMove,
    onMouseUp,
  };
}
