import type { DirectoryView } from './types';
import { VIEW_OPTIONS } from './types';

/**
 * État de l'annuaire porté par l'URL (/tree?view=tree&year=2021&q=…) : il survit au
 * retour arrière, se partage par lien, et la fiche personne peut y renvoyer.
 */
export interface DirectoryState {
  view: DirectoryView;
  q: string;
  years: number[];
  filieres: string[];
  schools: string[];
  alphabetical: boolean;
  /** Personne au centre de la vue Réseau */
  focus: number | null;
}

const VIEWS = new Set<string>(VIEW_OPTIONS.map((v) => v.value));
const STORAGE_KEY = 'parraindex:directory-search';

export function parseDirectoryParams(params: URLSearchParams): DirectoryState {
  const view = params.get('view') ?? 'grid';
  const focus = Number(params.get('focus'));
  return {
    view: VIEWS.has(view) ? (view as DirectoryView) : 'grid',
    q: params.get('q') ?? '',
    years: params
      .getAll('year')
      .map(Number)
      .filter((y) => Number.isInteger(y) && y > 0),
    filieres: params.getAll('filiere'),
    schools: params.getAll('school'),
    alphabetical: params.get('sort') === 'az',
    focus: Number.isInteger(focus) && focus > 0 ? focus : null,
  };
}

export function serializeDirectoryState(state: DirectoryState): URLSearchParams {
  const params = new URLSearchParams();
  if (state.view !== 'grid') params.set('view', state.view);
  if (state.q) params.set('q', state.q);
  state.years.forEach((y) => {
    params.append('year', String(y));
  });
  state.filieres.forEach((f) => {
    params.append('filiere', f);
  });
  state.schools.forEach((s) => {
    params.append('school', s);
  });
  if (state.alphabetical) params.set('sort', 'az');
  if (state.focus !== null && state.view === 'ego') params.set('focus', String(state.focus));
  return params;
}

/** Mémorise la dernière recherche de l'annuaire (pour le fil d'Ariane de la fiche) */
export function rememberDirectorySearch(search: string): void {
  try {
    sessionStorage.setItem(STORAGE_KEY, search);
  } catch {
    // stockage indisponible (navigation privée…) : on retombera sur /tree
  }
}

/** Lien vers l'annuaire dans l'état où l'utilisateur l'a laissé */
export function directoryHref(): string {
  try {
    const search = sessionStorage.getItem(STORAGE_KEY);
    return search ? `/tree?${search}` : '/tree';
  } catch {
    return '/tree';
  }
}
