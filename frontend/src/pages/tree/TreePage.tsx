import { useEffect, useRef, useState } from 'react';
import type { MouseEvent } from 'react';
import { useNavigate } from 'react-router';
import { PersonCard } from '../../components/PersonCard';
import { PersonCardSkeleton } from '../../components/PersonCardSkeleton';
import { useCarousel } from '../../hooks/useCarousel';
import { usePersonFilter } from '../../hooks/usePersonFilter';
import { getTreePage } from '../../lib/api/tree';
import { getYearRange } from '../../lib/persons';
import type { PersonSummary } from '../../types/person';

const SKELETON_COUNT = 10;
const PAGE_SIZE = 20;

export function TreePage() {
  const [persons, setPersons] = useState<PersonSummary[]>([]);
  const [loading, setLoading] = useState(true);
  const [total, setTotal] = useState<number | null>(null);
  const [searchOpen, setSearchOpen] = useState(false);
  const searchInputRef = useRef<HTMLInputElement>(null);
  const navigate = useNavigate();

  const { name, year, alphabetical, filtered, setName, setYear, toggleAlphabetical } =
    usePersonFilter(persons);

  const {
    sliderRef,
    dragStartX,
    scrollProgress,
    centeredIndex,
    onScroll,
    onProgressChange,
    onMouseDown,
    onMouseMove,
    onMouseUp,
  } = useCarousel(filtered.length);

  useEffect(() => {
    const cancel: { current: boolean } = { current: false };

    async function loadAll() {
      const first = await getTreePage(1, PAGE_SIZE);
      if (cancel.current) return;

      if (!first.ok) {
        setLoading(false);
        return;
      }

      setPersons(first.data.items);
      setTotal(first.data.total);
      setLoading(false);

      const pages = Math.ceil(first.data.total / PAGE_SIZE);
      for (let page = 2; page <= pages; page++) {
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        if (cancel.current) break;
        const result = await getTreePage(page, PAGE_SIZE);
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        if (cancel.current) break;
        if (result.ok) {
          setPersons((prev) => [...prev, ...result.data.items]);
        }
      }
    }

    void loadAll();
    return () => {
      cancel.current = true;
    };
  }, []);

  useEffect(() => {
    if (searchOpen) searchInputRef.current?.focus();
  }, [searchOpen]);

  const yearRange = getYearRange(persons);
  const currentYear = new Date().getFullYear();

  function handleYearChange(delta: number) {
    const base = year ?? currentYear;
    const next = base + delta;
    if (yearRange !== null && next >= yearRange.min && next <= yearRange.max) {
      setYear(next);
    }
  }

  function handleCardClick(personId: number, e: MouseEvent) {
    const sliderOffsetLeft = sliderRef.current?.offsetLeft ?? 0;
    const x = e.pageX - sliderOffsetLeft;
    if (Math.abs(x - dragStartX.current) > 5) return;
    void navigate(`/personne/${personId}`);
  }

  const loadingMore = !loading && total !== null && persons.length < total;

  return (
    <div className="flex h-[calc(100vh-3.5rem)] flex-col bg-light-grey">
      {/* Carousel */}
      <section className="relative flex-1 overflow-hidden">
        {/* Fade edges */}
        <div className="pointer-events-none absolute inset-y-0 left-0 z-20 w-16 bg-gradient-to-r from-light-grey to-transparent" />
        <div className="pointer-events-none absolute inset-y-0 right-0 z-20 w-16 bg-gradient-to-l from-light-grey to-transparent" />

        {loading ? (
          <div className="flex h-full select-none items-center gap-6 overflow-x-hidden px-10 py-8">
            {Array.from({ length: SKELETON_COUNT }, (_, i) => (
              <PersonCardSkeleton key={i} />
            ))}
          </div>
        ) : (
          <div
            ref={sliderRef}
            className="carousel__slider flex h-full cursor-grab select-none items-center gap-6 overflow-x-auto px-10 py-8 active:cursor-grabbing"
            style={{ scrollbarWidth: 'none' }}
            onScroll={onScroll}
            onMouseDown={onMouseDown}
            onMouseMove={onMouseMove}
            onMouseUp={onMouseUp}
            onMouseLeave={onMouseUp}
          >
            {filtered.length === 0 ? (
              <div className="mx-auto text-sm text-medium-grey">Aucun résultat trouvé</div>
            ) : (
              filtered.map((person, i) => (
                <PersonCard
                  key={person.id}
                  person={person}
                  isCentered={i === centeredIndex}
                  onClick={(e) => {
                    handleCardClick(person.id, e);
                  }}
                  animationDelay={Math.min(i * 35, 280)}
                />
              ))
            )}
          </div>
        )}
      </section>

      {/* Barre inférieure */}
      <div className="flex flex-col border-t border-medium-grey/30 bg-white shadow-md">
        {/* Scrollbar */}
        <div className="flex items-center gap-3 px-6 pt-3">
          <span className="w-5 text-right text-xs tabular-nums text-medium-grey">1</span>
          <input
            type="range"
            min={0}
            max={100}
            step={0.01}
            value={scrollProgress * 100}
            onChange={(e) => {
              onProgressChange(Number(e.target.value) / 100);
            }}
            className="tree-slider flex-1"
            disabled={loading}
          />
          <span className="flex w-12 items-center gap-1 text-xs tabular-nums text-medium-grey">
            {loading ? '…' : filtered.length}
            {loadingMore && <span className="animate-pulse text-light-blue">·</span>}
          </span>
        </div>

        {/* Contrôles */}
        <nav
          className={[
            'flex flex-wrap items-center gap-3 px-6 py-3 transition-opacity',
            loading ? 'pointer-events-none opacity-30' : '',
          ].join(' ')}
        >
          {/* Recherche */}
          <div className="flex items-center gap-2">
            <button
              onClick={() => {
                setSearchOpen((v) => !v);
              }}
              aria-label="Rechercher"
              className={[
                'flex h-8 w-8 items-center justify-center rounded-lg transition-colors',
                searchOpen
                  ? 'bg-dark-blue text-white'
                  : 'text-medium-blue hover:bg-light-grey hover:text-dark-blue',
              ].join(' ')}
            >
              <svg width="14" height="14" viewBox="0 0 15 15" fill="none">
                <path
                  d="M10 6.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0ZM9.38 10.44a5 5 0 1 1 1.06-1.06l3.07 3.07a.75.75 0 1 1-1.06 1.06L9.38 10.44Z"
                  fill="currentColor"
                  fillRule="evenodd"
                  clipRule="evenodd"
                />
              </svg>
            </button>
            {searchOpen && (
              <input
                ref={searchInputRef}
                type="text"
                value={name}
                onChange={(e) => {
                  setName(e.target.value);
                }}
                placeholder="Rechercher…"
                className="h-8 rounded-lg border border-medium-grey/50 bg-light-grey px-3 text-sm text-dark-blue placeholder-medium-grey outline-none transition-colors focus:border-light-blue focus:bg-white"
              />
            )}
          </div>

          <div className="h-5 w-px bg-medium-grey/30" />

          {/* Spinner année */}
          <div className="flex items-center gap-1">
            <button
              onClick={() => {
                handleYearChange(-1);
              }}
              className="flex h-7 w-7 items-center justify-center rounded-lg text-medium-blue transition-colors hover:bg-light-grey hover:text-dark-blue"
              aria-label="Année précédente"
            >
              <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor">
                <path d="M5 2L9 7H1L5 2Z" />
              </svg>
            </button>
            <button
              onClick={() => {
                setYear(null);
              }}
              className="min-w-[5.5rem] rounded-lg px-2 py-1 text-center text-sm font-medium text-dark-blue transition-colors hover:bg-light-grey"
            >
              {year !== null ? `${year} / ${year + 1}` : '— / —'}
            </button>
            <button
              onClick={() => {
                handleYearChange(1);
              }}
              className="flex h-7 w-7 items-center justify-center rounded-lg text-medium-blue transition-colors hover:bg-light-grey hover:text-dark-blue"
              aria-label="Année suivante"
            >
              <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor">
                <path d="M5 8L1 3H9L5 8Z" />
              </svg>
            </button>
          </div>

          <div className="h-5 w-px bg-medium-grey/30" />

          {/* Tri alphabétique */}
          <button
            onClick={toggleAlphabetical}
            className={[
              'flex h-8 items-center gap-1.5 rounded-lg px-3 text-sm font-medium transition-colors',
              alphabetical
                ? 'bg-dark-blue text-white'
                : 'text-medium-blue hover:bg-light-grey hover:text-dark-blue',
            ].join(' ')}
          >
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
              <path
                d="M1 3h11M1 6.5h7M1 10h4"
                stroke="currentColor"
                strokeWidth="1.5"
                strokeLinecap="round"
              />
            </svg>
            {'A → Z'}
          </button>
        </nav>
      </div>
    </div>
  );
}
