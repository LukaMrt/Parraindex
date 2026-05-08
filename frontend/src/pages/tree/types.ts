export type DirectoryView = 'grid' | 'list' | 'timeline' | 'tree' | 'ego';

export interface ViewOption {
  value: DirectoryView;
  label: string;
  iconPath: string;
}

export const VIEW_OPTIONS: ViewOption[] = [
  {
    value: 'grid',
    label: 'Grille',
    iconPath: 'M3 3h6v6H3zm8 0h6v6h-6zm-8 8h6v6H3zm8 0h6v6h-6z',
  },
  {
    value: 'list',
    label: 'Liste',
    iconPath: 'M3 4h14M3 9h14M3 14h14',
  },
  {
    value: 'timeline',
    label: 'Timeline',
    iconPath: 'M5 3v14M5 5h10M5 10h7M5 15h10',
  },
  {
    value: 'tree',
    label: 'Arbre',
    iconPath: 'M10 3v4m-4 4V7h8v4m-8 0v4m8-4v4M3 15h4m6 0h4',
  },
  {
    value: 'ego',
    label: 'Réseau',
    iconPath:
      'M10 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0-6 0M10 10l-6-4M10 10l6-4M10 10l-6 4M10 10l6 4M4 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4M16 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4M4 18a2 2 0 1 0 0-4 2 2 0 0 0 0 4M16 18a2 2 0 1 0 0-4 2 2 0 0 0 0 4',
  },
];
