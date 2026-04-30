import { useEffect, useRef, useState } from 'react';
import type { MouseEvent } from 'react';
import { useNavigate } from 'react-router';
import { PersonCard } from '../../components/PersonCard';
import { useCarousel } from '../../hooks/useCarousel';
import { usePersonFilter } from '../../hooks/usePersonFilter';
import { getTree } from '../../lib/api/tree';
import { getYearRange } from '../../lib/persons';
import type { PersonSummary } from '../../types/person';

export function TreePage() {
  const [persons, setPersons] = useState<PersonSummary[]>([]);
  const [loading, setLoading] = useState(true);
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
    void getTree().then((result) => {
      if (result.ok) setPersons(result.data);
      setLoading(false);
    });
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

  return (
    <div className="flex h-[calc(100vh-3.5rem)] flex-col">
      {/* Carousel */}
      <section className="flex-1 overflow-hidden">
        {loading ? (
          <div className="flex h-full items-center justify-center text-medium-blue">
            {'Chargement…'}
          </div>
        ) : (
          <div
            ref={sliderRef}
            className="carousel__slider flex h-full cursor-grab select-none items-center gap-4 overflow-x-auto px-8 py-6 active:cursor-grabbing"
            style={{ scrollbarWidth: 'none' }}
            onScroll={onScroll}
            onMouseDown={onMouseDown}
            onMouseMove={onMouseMove}
            onMouseUp={onMouseUp}
            onMouseLeave={onMouseUp}
          >
            {filtered.length === 0 ? (
              <div className="mx-auto text-medium-blue">Aucun résultat trouvé</div>
            ) : (
              filtered.map((person, i) => (
                <PersonCard
                  key={person.id}
                  person={person}
                  isCentered={i === centeredIndex}
                  onClick={(e) => {
                    handleCardClick(person.id, e);
                  }}
                />
              ))
            )}
          </div>
        )}
      </section>

      {/* Scrollbar */}
      <nav className="flex items-center gap-2 bg-white px-6 py-2 shadow-inner">
        <span className="text-xs text-dark-grey">1</span>
        <input
          type="range"
          min={0}
          max={100}
          step={0.01}
          value={scrollProgress * 100}
          onChange={(e) => {
            onProgressChange(Number(e.target.value) / 100);
          }}
          className="flex-1"
        />
        <span className="text-xs text-dark-grey">{filtered.length}</span>
      </nav>

      {/* Contrôleur */}
      <nav className="flex flex-wrap items-center gap-4 bg-white px-6 py-3 shadow-md">
        {/* Recherche par nom */}
        <div className="flex items-center gap-2">
          <button
            onClick={() => {
              setSearchOpen((v) => !v);
            }}
            className="text-dark-blue"
            aria-label="Rechercher"
          >
            🔍
          </button>
          {searchOpen && (
            <input
              ref={searchInputRef}
              type="text"
              value={name}
              onChange={(e) => {
                setName(e.target.value);
              }}
              placeholder="Recherche"
              className="rounded border border-medium-grey px-3 py-1 text-sm text-dark-blue outline-none focus:border-light-blue"
            />
          )}
        </div>

        {/* Spinner année */}
        <div className="flex items-center gap-1">
          <button
            onClick={() => {
              handleYearChange(-1);
            }}
            className="rounded px-2 py-1 text-sm text-dark-blue hover:bg-light-grey"
          >
            ▲
          </button>
          <button
            onClick={() => {
              setYear(null);
            }}
            className="min-w-[5rem] text-center text-sm font-medium text-dark-blue"
          >
            {year !== null ? `${year} / ${year + 1}` : '— / —'}
          </button>
          <button
            onClick={() => {
              handleYearChange(1);
            }}
            className="rounded px-2 py-1 text-sm text-dark-blue hover:bg-light-grey"
          >
            ▼
          </button>
        </div>

        {/* Tri alphabétique */}
        <button
          onClick={toggleAlphabetical}
          className={[
            'rounded px-3 py-1 text-sm transition-colors',
            alphabetical
              ? 'bg-dark-blue text-white'
              : 'border border-dark-blue text-dark-blue hover:bg-light-grey',
          ].join(' ')}
        >
          {'Alphabétique'}
        </button>
      </nav>
    </div>
  );
}
