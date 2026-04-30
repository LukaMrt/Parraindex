# Plan de migration — Symfony/Twig → React SPA + Symfony API

## Contexte

Migration progressive du monolithe Symfony/Twig vers une architecture découplée :
- **Backend** : Symfony 7.2, API JSON artisanale, sessions HTTP, DTOs via ObjectMapper
- **Frontend** : React 19, Vite, TypeScript strict, Tailwind CSS 4.2, React Router

Chaque étape est indépendante et livrable séparément. L'application Twig reste fonctionnelle jusqu'à la bascule finale (étape 24).

---

## Phase 1 — Refactoring backend (sans API)

> Objectif : nettoyer l'architecture interne avant d'exposer quoi que ce soit. Les controllers Twig continuent de fonctionner pendant toute cette phase.

### Étape 1 — Extraction de la couche Service

**Quoi :** Créer `src/Service/` avec un service par domaine métier (`PersonService`, `SponsorService`, `ContactService`, `UserService`). Déplacer toute la logique métier hors des controllers.

**Structure cible :**
```
src/
  Service/
    PersonService.php
    SponsorService.php
    ContactService.php
    UserService.php
    AuthService.php
```

**Règle :** Les controllers ne font qu'orchestrer (valider la requête HTTP → appeler le service → retourner la réponse). Aucune logique métier dans un controller.

**Validation :** PHPStan niveau max passe, tests existants toujours verts.

---

### Étape 2 — Couche DTO + ObjectMapper

**Quoi :** Installer `symfony/object-mapper`. Créer les DTOs de réponse et de requête par domaine.

**Structure cible :**
```
src/
  Dto/
    Person/
      PersonResponseDto.php       # lecture (GET)
      PersonSummaryDto.php        # version allégée pour les listes
      PersonRequestDto.php        # écriture (POST/PUT)
    Sponsor/
      SponsorResponseDto.php
      SponsorRequestDto.php
    Contact/
      ContactRequestDto.php
    User/
      UserResponseDto.php         # profil de l'utilisateur connecté
    Auth/
      LoginRequestDto.php
      MeResponseDto.php
```

**Règle :** Les entités Doctrine ne sortent jamais des Services. Les controllers et l'API manipulent uniquement des DTOs.

**Mapping :** Configurer les `#[MapTo]` / `#[MapFrom]` sur les DTOs via le composant ObjectMapper. Les Services sont responsables d'appeler le mapper.

**Validation :** PHPStan niveau max passe.

---

### Étape 3 — Standardisation des erreurs et réponses

**Quoi :** Créer une couche de réponse JSON uniforme pour préparer l'API.

**Structure cible :**
```
src/
  Api/
    ApiResponse.php           # wrapper de réponse standard
    ApiError.php              # structure d'erreur standard
    ErrorCode.php             # enum des codes d'erreur métier
```

**Format de réponse uniforme :**
```json
// Succès
{ "data": { ... } }

// Erreur
{
  "error": {
    "code": "PERSON_NOT_FOUND",
    "message": "La personne demandée n'existe pas",
    "violations": []           // pour les erreurs de validation
  }
}
```

**Validation :** PHPStan niveau max passe.

---

## Phase 2 — Endpoints API

> Objectif : exposer l'API JSON sous `/api/`. Les controllers Twig coexistent. Chaque groupe d'endpoints est une étape indépendante.

### Étape 4 — Authentification API

**Quoi :** Endpoints de gestion de session.

**Endpoints :**
```
POST   /api/auth/login          # connexion (email + password)
POST   /api/auth/logout         # déconnexion
GET    /api/auth/me             # profil de l'utilisateur connecté
POST   /api/auth/register       # inscription
POST   /api/auth/verify-email   # vérification email (token)
POST   /api/auth/reset-password/request   # demande de reset
POST   /api/auth/reset-password/confirm  # confirmation reset
```

**Points d'attention :**
- Configurer le `firewall` Symfony pour retourner du JSON (pas de redirect) sur les routes `/api/*`
- Gérer le CSRF : utiliser un cookie `XSRF-TOKEN` lisible par le frontend (double-submit cookie pattern)
- Adapter `LoginSuccessListener` pour retourner un JSON avec `MeResponseDto` au lieu d'un redirect

**Validation :** Tests PHPUnit couvrant les cas nominaux et les cas d'erreur de chaque endpoint.

---

### Étape 5 — Endpoints Person

**Quoi :** CRUD complet pour les profils.

**Endpoints :**
```
GET    /api/persons             # liste paginée (filtres : nom, année)
GET    /api/persons/{id}        # profil complet
PUT    /api/persons/{id}        # mise à jour (voter PersonVoter)
DELETE /api/persons/{id}        # suppression (voter AdminVoter)
POST   /api/persons/{id}/picture  # upload photo (multipart)
GET    /api/persons/{id}/export   # export RGPD (DataController)
```

**Points d'attention :**
- Réutiliser les Voters existants (`PersonVoter`, `AdminVoter`) — ils ne changent pas
- La liste doit retourner `PersonSummaryDto[]` (pas le profil complet) pour limiter le payload
- Upload photo : endpoint dédié en multipart, retourne l'URL de la nouvelle photo

**Validation :** PHPStan niveau max + tests PHPUnit.

---

### Étape 6 — Endpoints Sponsor

**Quoi :** CRUD des relations de parrainage.

**Endpoints :**
```
GET    /api/sponsors/{id}        # détail d'un parrainage
POST   /api/sponsors             # créer un parrainage
PUT    /api/sponsors/{id}        # modifier (voter SponsorVoter)
DELETE /api/sponsors/{id}        # supprimer (voter SponsorVoter)
```

**Validation :** PHPStan niveau max + tests PHPUnit.

---

### Étape 7 — Endpoints Contact & Admin

**Quoi :** Formulaires de contact et actions d'administration.

**Endpoints :**
```
POST   /api/contact              # soumettre une demande

GET    /api/admin/contacts       # liste des demandes (AdminVoter)
PUT    /api/admin/contacts/{id}  # traiter une demande (AdminVoter)
POST   /api/admin/persons        # créer une personne (AdminVoter)
```

**Validation :** PHPStan niveau max + tests PHPUnit.

---

### Étape 8 — Endpoint Tree (données agrégées)

**Quoi :** Endpoint dédié à l'affichage de l'arbre de parrainage. Contient la logique de récupération optimisée (JOIN FETCH pour éviter le N+1).

**Endpoints :**
```
GET    /api/tree                 # toutes les personnes avec leurs liens de parrainage
```

**Réponse :** Structure optimisée pour le rendu du carousel/graphe côté React (personnes + leurs parrains/filleuls directs).

**Validation :** PHPStan niveau max + tests PHPUnit sur la structure de la réponse.

---

## Phase 3 — Setup Frontend

> Objectif : mettre en place le projet React avec toute la chaîne de qualité avant d'écrire une seule page.

### Étape 9 — Initialisation du projet Vite + React + TypeScript

**Quoi :** Créer le projet frontend dans `frontend/`.

**Stack :**
- Vite 6 (dernière version)
- React 19
- TypeScript 5 en mode `strict`

**Config TypeScript (`tsconfig.json`) :**
```json
{
  "compilerOptions": {
    "strict": true,
    "noUncheckedIndexedAccess": true,
    "exactOptionalPropertyTypes": true,
    "noImplicitReturns": true,
    "noFallthroughCasesInSwitch": true
  }
}
```

**Structure cible :**
```
frontend/
  src/
    components/     # composants React purs (UI uniquement)
    hooks/          # hooks React (logique stateful)
    lib/            # fonctions pures sans React (api client, utils)
    pages/          # un dossier par route
    types/          # types TypeScript partagés (DTOs, enums)
    router.tsx      # définition des routes React Router
    main.tsx        # point d'entrée
  public/
  index.html
  vite.config.ts
  tsconfig.json
```

**Validation :** `tsc --noEmit` sans erreur, `vite build` sans erreur.

---

### Étape 10 — Tooling qualité

**Quoi :** Configurer ESLint, Prettier et Tailwind CSS 4.2.

**ESLint :**
- `@typescript-eslint/eslint-plugin` en mode `strict` + `stylistic`
- `eslint-plugin-react-hooks` (règles exhaustive-deps obligatoires)
- `eslint-plugin-react` (mode JSX transform, pas d'import React requis)
- Règle : pas de `any` implicite ou explicite, pas de `// @ts-ignore`

**Prettier :** config standard (semi, singleQuote, trailingComma).

**Tailwind CSS 4.2 :**
- Configuration via `@theme` CSS (variables CSS natives, pas de `tailwind.config.js`)
- Reprendre les variables de couleur existantes (`--dark-blue`, `--light-grey`, etc.) comme design tokens
- Breakpoints alignés sur ceux de l'app actuelle (700px, 900px, 1300px)

**Scripts `package.json` :**
```json
{
  "lint": "eslint src --max-warnings 0",
  "format": "prettier --write src",
  "typecheck": "tsc --noEmit"
}
```

**Validation :** `lint`, `typecheck` et `format --check` passent tous à zéro warning.

---

### Étape 11 — Couche API client

**Quoi :** Créer un client HTTP typé dans `src/lib/api/`.

**Structure :**
```
src/lib/api/
  client.ts          # fetch wrapper (gestion CSRF, erreurs, types)
  auth.ts            # appels /api/auth/*
  persons.ts         # appels /api/persons/*
  sponsors.ts        # appels /api/sponsors/*
  contact.ts         # appels /api/contact
  tree.ts            # appels /api/tree
  admin.ts           # appels /api/admin/*
```

**Règles :**
- Chaque fonction retourne `Promise<Result<T, ApiError>>` (type Result maison, pas d'exception throw)
- Les types de réponse dans `src/types/` correspondent exactement aux DTOs Symfony
- Le client gère automatiquement le header CSRF (lit le cookie `XSRF-TOKEN`)
- Fonctions pures, zéro import React → testables avec Vitest sans setup DOM

**Validation :** `tsc --noEmit` sans erreur, tests unitaires Vitest pour le client.

---

### Étape 12 — Auth context + routing

**Quoi :** Mettre en place React Router et la gestion de session.

**Structure :**
```
src/
  hooks/
    useAuth.ts         # état de l'utilisateur connecté
  components/
    ProtectedRoute.tsx # garde de route (redirige si non connecté)
  pages/
    auth/
      LoginPage.tsx
      RegisterPage.tsx
      CheckEmailPage.tsx
      ResetPasswordPage.tsx
  router.tsx
```

**`useAuth` :** Expose `{ user, login, logout, isLoading }`. Stocké dans un `AuthContext`. Appelle `GET /api/auth/me` au montage pour restaurer la session.

**Routing :** React Router v7, deux layouts — `PublicLayout` (sans barre de navigation) et `AppLayout` (avec navigation, wrappé dans `ProtectedRoute`).

**Validation :** TypeScript strict, `lint` zéro warning, test du hook `useAuth` avec Vitest.

---

## Phase 4 — Migration des pages

> Objectif : recréer chaque page en React. L'ordre est du plus simple au plus complexe. Chaque page est indépendante.

### Étape 13 — Pages statiques

Pages sans état ni appel API : Home, About, Mentions légales.

---

### Étape 14 — Tree / Carousel

Page la plus interactive. Appelle `GET /api/tree`.

**Points d'attention :**
- Réécrire le carousel (actuellement 310 lignes JS vanilla) en composants React avec hooks dédiés
- Hook `useCarousel` (scroll, position) dans `src/hooks/`
- Hook `usePersonFilter` (filtres nom, année, alpha) dans `src/hooks/`
- Logique de filtrage dans `src/lib/persons.ts` (fonctions pures, testables)

---

### Étape 15 — Profil Person

Page `GET /api/persons/{id}`. Affichage du profil complet avec ses parrainages.

---

### Étape 16 — Édition profil (EditPerson)

Page protégée. Appelle `PUT /api/persons/{id}` et `POST /api/persons/{id}/picture`.

**Points d'attention :**
- Réécrire le live-preview (363 lignes JS vanilla) avec état React local
- Upload photo via `FormData`

---

### Étape 17 — Parrainage (Sponsor + EditSponsor)

Pages de visualisation et d'édition d'un parrainage.

---

### Étape 18 — Contact & Admin

Formulaire de contact et interface d'administration.

---

## Phase 5 — Tests frontend

### Étape 19 — Setup Vitest

**Quoi :** Configurer Vitest avec l'environnement approprié par type de fichier.

**Config :**
- `jsdom` pour les tests de hooks (accès au DOM)
- `node` pour les tests de `lib/` (fonctions pures, zéro DOM)
- `@testing-library/react` pour les hooks avec état React
- Coverage avec `v8`

**Convention de nommage :**
```
src/lib/persons.ts        → src/lib/persons.test.ts    (env: node)
src/hooks/useAuth.ts      → src/hooks/useAuth.test.ts  (env: jsdom)
src/components/Card.tsx   → src/components/Card.test.tsx (env: jsdom)
```

---

### Étape 20 — Tests des libs

Couvrir `src/lib/` en priorité : client API, utils de filtrage, formatage.

**Règle :** 100% des fonctions exportées de `src/lib/` ont des tests. Ces tests sont rapides (env `node`, pas de DOM).

---

### Étape 21 — Tests des hooks

Couvrir les hooks critiques : `useAuth`, `useCarousel`, `usePersonFilter`.

---

## Phase 6 — Déploiement et bascule

### Étape 22 — Configuration Docker + Nginx

**Quoi :** Mettre à jour le container pour servir les deux apps.

**Architecture dans le container :**
```
Nginx
  ├── /api/*    → reverse proxy vers FrankenPHP (Symfony)
  └── /*        → fichiers statiques du build Vite (SPA fallback sur index.html)
```

**Points d'attention :**
- `try_files $uri /index.html` pour le fallback SPA (React Router gère les routes)
- Headers de cache appropriés (assets Vite sont hashés → cache long, `index.html` → no-cache)

---

### Étape 23 — CI/CD

**Quoi :** Mettre à jour le pipeline CI pour builder et valider les deux apps.

**Jobs à ajouter :**
```
frontend-quality:
  - npm ci
  - npm run typecheck
  - npm run lint
  - npm run test

frontend-build:
  - npm run build
```

---

### Étape 24 — Bascule et suppression de Twig

**Quoi :** Dernière étape. Supprimer tout le code Twig une fois le frontend React validé en production.

**Checklist :**
- Supprimer `templates/`
- Supprimer `assets/scripts/` et `assets/styles/`
- Supprimer les controllers Twig (remplacés par les controllers API)
- Supprimer les `Form/` Symfony (remplacés par la validation côté DTOs)
- Supprimer `twig/extra-bundle`, `asset-mapper`, les dépendances Twig de `composer.json`
- Supprimer `importmap.php`

---

## Récapitulatif des étapes

| #   | Phase       | Étape                     | Dépend de |
| --- | ----------- | ------------------------- | --------- |
| 1   | Backend     | Extraction Services       | —         |
| 2   | Backend     | DTOs + ObjectMapper       | 1         |
| 3   | Backend     | Standardisation erreurs   | 2         |
| 4   | API         | Auth                      | 3         |
| 5   | API         | Endpoints Person          | 3         |
| 6   | API         | Endpoints Sponsor         | 3         |
| 7   | API         | Endpoints Contact & Admin | 3         |
| 8   | API         | Endpoint Tree             | 5, 6      |
| 9   | Frontend    | Init Vite + React + TS    | —         |
| 10  | Frontend    | Tooling qualité           | 9         |
| 11  | Frontend    | API client                | 9, 10     |
| 12  | Frontend    | Auth context + routing    | 11, 4     |
| 13  | Frontend    | Pages statiques           | 12        |
| 14  | Frontend    | Tree / Carousel           | 12, 8     |
| 15  | Frontend    | Profil Person             | 12, 5     |
| 16  | Frontend    | Édition profil            | 15, 5     |
| 17  | Frontend    | Sponsor                   | 15, 6     |
| 18  | Frontend    | Contact & Admin           | 12, 7     |
| 19  | Tests       | Setup Vitest              | 10        |
| 20  | Tests       | Tests libs                | 11, 19    |
| 21  | Tests       | Tests hooks               | 12, 19    |
| 22  | Déploiement | Docker + Nginx            | 14–18     |
| 23  | Déploiement | CI/CD                     | 19–21     |
| 24  | Déploiement | Bascule finale            | 22, 23    |
