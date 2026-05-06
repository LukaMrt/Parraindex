# Frontend — Parraindex

Stack : React 19 + TypeScript + Vite + Tailwind CSS v4.

## Design system

### Tokens (`src/styles/tokens.css`)
Définis dans `@theme {}` → disponibles comme classes Tailwind (`text-ink`, `bg-surface`, `border-line`) **et** comme CSS vars (`var(--color-ink)`).

Couleurs sémantiques : `bg`, `surface`, `line`, `ink`, `ink-2`, `ink-3`, `ink-4`.  
Mode sombre : `[data-theme='dark']` sur `<body>`.

### Couleurs de promo (`src/lib/colors.ts`)
Ne jamais lier une couleur à une année précise. Utiliser `promoColor(startYear)` qui fait une rotation sur `PROMO_PALETTE` (9 couleurs). Les couleurs dynamiques venant de l'API s'appliquent via **inline styles**, pas des classes Tailwind.

### Helpers
- `cn(...classes)` — `src/lib/cn.ts` (clsx)
- `contrastColor(hex)` — retourne `#000` ou `#fff` lisible sur un fond donné

### Composants UI (`src/components/ui/`)
Primitives réexportées depuis `src/components/ui/index.ts` :

| Composant     | Variantes notables                                                                                                               |
| ------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| `Avatar`      | `square`, `fill` (remplit le parent)                                                                                             |
| `Button`      | `primary`, `secondary`, `ghost`, `danger`, `pill-neutral`, `pill-active`, `pill-color` + sizes `sm/md/lg/icon` — utilise **cva** |
| `Badge`       | `default`, `promo`, `status`, `type` — prop `color` pour dynamique                                                               |
| `Card`        | `radius` (lg/xl/2xl), `padding`, `hoverable`, `hoverColor`                                                                       |
| `Input`       | prop `leadingIcon` pour icône à gauche                                                                                           |
| `Modal`       | gère Escape + clic backdrop                                                                                                      |
| `StatCard`    | label uppercase + grande valeur                                                                                                  |
| `TabSwitcher` | générique `<T extends string>`                                                                                                   |
| `Breadcrumb`  | tableau d'items avec `onClick` optionnel                                                                                         |
| `EmptyState`  | prop `dashed` pour bordure pointillée                                                                                            |

### Règles
- Toujours importer depuis `../../components/ui` (barrel), pas les fichiers individuels.
- Les classes Tailwind pour les couleurs statiques ; `style={{}}` pour les couleurs dynamiques (person.color).
- Pas de `tailwind-merge` — utiliser `cn()` (clsx suffit ici).
