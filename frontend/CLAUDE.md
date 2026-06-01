# Frontend — Parraindex

Stack : React 19 + TypeScript strict + Vite + Tailwind CSS v4 + React Router 7 + TanStack Query 5.

## Structure
```
src/
  components/   # Composants React purs (ui/ = primitives du design system)
  context/      # Contextes (AuthContext, NotificationContext)
  hooks/        # Hooks réutilisables (useAuth, usePersonFilter, usePanZoom…)
  lib/          # Sans React : api/ (client + endpoints), queries.ts, helpers (cn, colors…)
  pages/        # Un dossier par route ; sous-composants et hooks co-localisés
  types/        # Types partagés (api.ts, person.ts, sponsor.ts, auth.ts)
  router.tsx    # Routes React Router
```

## Couche données (API + TanStack Query)

### Client (`src/lib/api/client.ts`)
`get/post/put/patch/del/postFormData` enveloppent `fetch` et renvoient un **`Result<T>`** discriminé : `{ ok: true, data }` | `{ ok: false, error }` (jamais de throw sur erreur réseau/HTTP). Le CSRF est injecté automatiquement (cookie `XSRF-TOKEN` → header `X-XSRF-TOKEN`), `credentials: 'same-origin'`. URLs **relatives** (`/api/...`) ; le proxy Vite route vers le backend en dev.

Un fichier par domaine dans `lib/api/` (`persons.ts`, `sponsors.ts`, `home.ts`…) expose les appels typés.

### Enveloppe & erreurs
Le backend répond `{ data }` ou `{ error: { code, message, violations } }`. Le type `ErrorCode` (`src/types/api.ts`) est le **miroir exact** de l'enum PHP `App\Api\ErrorCode` — synchroniser les deux.

### Queries (`src/lib/queries.ts`)
Les query options sont **centralisées** par domaine (`personQueries.detail(id)`, `homeQueries.stats()`…) avec `queryKey`/`queryFn`/`staleTime`. Le helper `throwable(result)` convertit un `Result<T>` en `T` (ou throw) pour TanStack Query, qui gère le cache/loading/erreur. Ne pas appeler le client directement depuis un composant : passer par une query option.

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
