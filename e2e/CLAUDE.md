# E2E — Parraindex

Tests end-to-end Playwright (TypeScript) qui tournent contre la **stack Docker complète** (backend + frontend buildé + MySQL + Mailpit), pas contre des mocks.

## Lancer les tests

Depuis la **racine du repo**, via le `justfile` :

```bash
just e2e            # up stack de test + install + run (idempotent, recommandé)
just e2e-up         # démarre la stack (compose.yaml + compose.e2e.yaml)
just e2e-test       # lance les tests (stack déjà up)
just e2e-ui         # mode interactif Playwright (watch, traces)
```

Mode dev (hot-reload Vite sur `:3000`) : `just e2e-up-dev` puis `just e2e-test-dev`.

`baseURL` par défaut : `http://localhost` (surchargeable via `E2E_BASE_URL`).

## Structure

```
tests/        # Specs groupées par domaine (auth/, profile/, tree/, admin/, public/)
helpers/      # Utilitaires partagés
playwright.config.ts
```

### Helpers (`helpers/`)

- `auth.ts` — `loginAs(page, email, password)` via l'UI ; constantes de comptes fixtures (`LUKA_EMAIL`, `LILIAN_EMAIL`, `DEFAULT_PASSWORD`).
- `fixtures.ts` — `resetFixtures()` (recharge `doctrine:fixtures:load` dans le conteneur `app`), `clearMailpit()`.
- `mailpit.ts` — lecture des mails reçus (API Mailpit `:8025`) pour tester register / reset password.
- `assert.ts` — `assertDefined(value, msg)` au lieu de `!` (non-null assertion interdite par ESLint).

## Conventions

- **Données** : les tests s'appuient sur les fixtures backend (`UserFixture`, `PersonFixture`…). Recharger via `resetFixtures()` quand un test mute l'état.
- **Sélecteurs** : privilégier les rôles/labels accessibles (`getByRole`, `getByPlaceholder`) plutôt que les sélecteurs CSS.
- **Exécution séquentielle** : `fullyParallel: false`, `workers: 1` (état partagé en BDD). En CI, 2 retries + traces/screenshots/vidéo on failure.
- Qualité (mêmes règles que le frontend) : `npm run lint` / `npm run typecheck` / `npm run format:check` — la CI les vérifie.

## Couverture actuelle

auth (login, register manuel/auto, conflits, reset password), accès admin (autorisé/interdit), profil (édition infos, filières, associations, parrainages), annuaire/arbre (vues, filtres, navigation famille), navigation publique.
