import { describe, it, expect } from 'vitest';
import { renderHook, act } from '@testing-library/react';
import type { MouseEvent } from 'react';
import { useCarousel } from './useCarousel';

function makeDiv(scrollWidth = 1000, clientWidth = 200, scrollLeft = 0): HTMLDivElement {
  const div = document.createElement('div');
  Object.defineProperties(div, {
    scrollWidth: { value: scrollWidth, writable: true },
    clientWidth: { value: clientWidth, writable: true },
    scrollLeft: { value: scrollLeft, writable: true },
    offsetLeft: { value: 0, writable: true },
  });
  return div;
}

describe('useCarousel', () => {
  it('initialise avec scrollProgress=0.5 et centeredIndex=0', () => {
    const { result } = renderHook(() => useCarousel(5));
    expect(result.current.scrollProgress).toBe(0.5);
    expect(result.current.centeredIndex).toBe(0);
  });

  it('expose sliderRef et dragStartX comme refs', () => {
    const { result } = renderHook(() => useCarousel(5));
    expect(result.current.sliderRef).toBeDefined();
    expect(result.current.dragStartX).toBeDefined();
  });

  it('onScroll met à jour scrollProgress et centeredIndex', () => {
    const { result } = renderHook(() => useCarousel(5));
    const div = makeDiv(1000, 200, 400);
    // @ts-expect-error — sliderRef.current est readonly, on l'écrase pour le test
    result.current.sliderRef.current = div;
    act(() => {
      result.current.onScroll();
    });
    // scrollLeft=400, max=800 → progress=0.5 → centeredIndex=round(0.5*4)=2
    expect(result.current.scrollProgress).toBeCloseTo(0.5);
    expect(result.current.centeredIndex).toBe(2);
  });

  it('onProgressChange met à jour scrollLeft', () => {
    const { result } = renderHook(() => useCarousel(5));
    const div = makeDiv(1000, 200, 0);
    // @ts-expect-error — sliderRef.current est readonly, on l'écrase pour le test
    result.current.sliderRef.current = div;
    act(() => {
      result.current.onProgressChange(1);
    });
    expect(div.scrollLeft).toBe(800);
    expect(result.current.scrollProgress).toBe(1);
  });

  it('onProgressChange avec sliderRef null ne plante pas', () => {
    const { result } = renderHook(() => useCarousel(5));
    expect(() => {
      act(() => {
        result.current.onProgressChange(0.5);
      });
    }).not.toThrow();
  });

  it('onMouseDown et onMouseUp gèrent le drag', () => {
    const { result } = renderHook(() => useCarousel(5));
    const div = makeDiv();
    // @ts-expect-error — sliderRef.current est readonly, on l'écrase pour le test
    result.current.sliderRef.current = div;

    act(() => {
      result.current.onMouseDown({ pageX: 100 } as MouseEvent);
    });
    expect(result.current.dragStartX.current).toBe(100);

    act(() => {
      result.current.onMouseUp();
    });
  });

  it('calcule centeredIndex=0 pour itemCount=1', () => {
    const { result } = renderHook(() => useCarousel(1));
    const div = makeDiv(1000, 200, 400);
    // @ts-expect-error — sliderRef.current est readonly, on l'écrase pour le test
    result.current.sliderRef.current = div;
    act(() => {
      result.current.onProgressChange(1);
    });
    expect(result.current.centeredIndex).toBe(0);
  });
});
