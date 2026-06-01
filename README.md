# Parraindex

[![Version](https://img.shields.io/badge/version-2.0.0-blue)]()
[![Quality](https://github.com/LukaMrt/Parraindex/actions/workflows/build.yml/badge.svg)](https://github.com/LukaMrt/Parraindex/actions/workflows/build.yml)
![GitHub language count](https://img.shields.io/github/languages/count/lukamrt/parraindex)
![GitHub](https://img.shields.io/github/license/lukamrt/parraindex)
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-5-orange.svg?style=flat)](#contributors)
<!-- ALL-CONTRIBUTORS-BADGE:END -->

## À propos

Parraindex est une application web qui visualise les relations de parrainage entre étudiants de l'IUT Lyon 1. Chaque étudiant peut créer son profil, voir les liens qui existent entre les personnes, et soumettre des demandes de parrainage validées par les administrateurs.


## Architecture

L'application suit une architecture **SPA découplée** :

```
parraindex/
├── backend/       # API Symfony 8 + admin EasyAdmin — FrankenPHP
├── frontend/      # SPA React 19 — Vite
├── e2e/           # Tests end-to-end Playwright
├── docker/        # Dockerfile + Caddyfile (prod)
├── justfile       # Tâches courantes (e2e, build Docker)
└── compose.yaml   # Orchestration Docker (prod)
```

**Backend** — `backend/`

| Technologie  | Version | Rôle                                   |
| ------------ | ------- | -------------------------------------- |
| PHP          | 8.5     | Runtime                                |
| Symfony      | 8       | Framework HTTP + DI                    |
| FrankenPHP   | 1.12    | Serveur HTTP (Caddy, remplace PHP-FPM) |
| Doctrine ORM | 3       | Accès base de données                  |
| EasyAdmin    | 5       | Interface d'administration (`/admin`)  |
| MySQL        | 9       | Base de données                        |

Structure interne du backend :
```
backend/src/
  Api/             # Wrappers de réponse JSON (ApiResponse, ApiError, ErrorCode)
  Controller/Api/  # Controllers API (orchestration uniquement, pas de logique métier)
  Controller/Admin/# Back-office EasyAdmin (CRUD, dashboard, import CSV, fusion)
  Command/         # Commandes console (reset déploiement, reset mot de passe)
  Dto/             # DTOs de requête et réponse (Person, Sponsor, Contact, Auth…)
  Entity/          # Entités Doctrine
  EventListener/   # Listeners Doctrine (nettoyage des logos)
  Fixture/         # Données de démonstration
  Form/            # Types de formulaires Symfony (back-office)
  Repository/      # Accès aux données
  Security/        # Voters + handlers d'authentification API
  Service/         # Logique métier (PersonService, ContactResolver…)
```

**Frontend** — `frontend/`

| Technologie    | Version  | Rôle                      |
| -------------- | -------- | ------------------------- |
| React          | 19       | UI                        |
| Vite           | 8        | Bundler / dev server      |
| TypeScript     | 6 strict | Typage                    |
| Tailwind CSS   | 4        | Styles                    |
| React Router   | 7        | Routing SPA               |
| TanStack Query | 5        | Cache & data fetching API |
| Vitest         | 4        | Tests unitaires           |

Structure interne du frontend :
```
frontend/src/
  components/   # Composants React purs (UI uniquement)
  context/      # Contextes React (AuthContext…)
  hooks/        # Hooks React (useAuth, useCarousel, usePersonFilter…)
  lib/          # Fonctions pures sans React (api client, utils)
  pages/        # Un dossier par route
  types/        # Types TypeScript partagés (DTOs, enums)
  router.tsx    # Définition des routes React Router
  main.tsx      # Point d'entrée
```

**En production**, un conteneur unique FrankenPHP (basé sur Caddy) sert à la fois l'API et la SPA. Le build Docker compile le frontend (`vite build`) et copie le `dist/` dans l'image ; Caddy route les requêtes :
```
FrankenPHP / Caddy :80
  ├── /api/*  →  Symfony (API JSON)
  ├── /admin/*→  Symfony (back-office EasyAdmin)
  └── /*      →  dist/ React (SPA fallback index.html)
```

---

## Développement local (sans Docker)

### Prérequis

- PHP 8.5+ avec extensions `pdo_mysql`, `intl`, `ctype`, `iconv`
- Composer 2
- Node.js 22+ et npm
- MySQL 9 (ou MariaDB 10.11+)
- Symfony CLI (recommandé pour le serveur de développement)

### 1 — Cloner le dépôt

```bash
git clone https://github.com/LukaMrt/Parraindex.git
cd Parraindex
```

### 2 — Configurer le backend

```bash
cd backend
composer install
```

Créer le fichier `.env.local` (jamais commité). La configuration utilise des variables séparées (pas de DSN) :

```properties
APP_ENV=dev
APP_SECRET=une_chaine_aleatoire_longue

DATABASE_DRIVER=mysql
DATABASE_HOST=127.0.0.1
DATABASE_PORT=3306
DATABASE_USER=parraindex
DATABASE_PASSWORD=parraindex
DATABASE_NAME=parraindex

MAIL_HOST=localhost
MAIL_PORT=1025          # Mailpit en local
MAIL_USER=admin@localhost.com
MAIL_PASSWORD=password
MAIL_NAME=Parraindex
```

Initialiser la base de données :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load   # données de test (optionnel)
```

Démarrer le serveur backend :

```bash
symfony serve --port=8000
# ou sans la CLI Symfony :
php -S localhost:8000 public/index.php
```

L'API répond sur `http://localhost:8000/api/`.

### 3 — Configurer le frontend

```bash
cd ../frontend
npm install
```

Le frontend appelle des URL relatives (`/api`, `/admin`, `/uploads`…) : en développement, le serveur Vite les **proxifie** vers le backend (cf. `vite.config.ts`). La cible par défaut est `https://127.0.0.1:8000` ; pour viser un autre backend, définir `VITE_API_BASE_URL` dans `.env.local` :

```properties
VITE_API_BASE_URL=http://localhost:8000
```

Démarrer le serveur de développement :

```bash
npm run dev
```

Le frontend répond sur `http://localhost:3000` avec hot-reload.

### Commandes utiles (développement)

**Backend :**

```bash
# Qualité
composer phpstan          # Analyse statique (PHPStan niveau 10)
composer phpcs            # Style de code (PHP-CS)
composer test             # Tests PHPUnit (Unit / Integration / Functional)
php vendor/bin/infection  # Tests de mutation

# Base de données
composer migration:run    # Appliquer les migrations
composer migration:new    # Générer une migration (make:migration)
composer database:reset   # Drop + create + migrate + fixtures
```

**Frontend :**

```bash
npm run dev           # Serveur de développement
npm run typecheck     # Vérification TypeScript
npm run lint          # ESLint (0 warning toléré)
npm run format        # Prettier
npm run test          # Vitest (watch mode)
npm run test:coverage # Rapport de couverture
npm run build         # Build de production
```

**Tests end-to-end (Playwright) :**

Les tests e2e tournent contre la stack Docker complète. Les recettes sont dans le `justfile` :

```bash
just e2e            # Stack de test + install + run (idempotent)
just e2e-up-dev     # Stack en mode dev (hot-reload Vite sur :3000)
just e2e-test-dev   # Lancer les tests contre la stack dev
just e2e-ui         # Mode interactif Playwright (watch, traces)
```

---

## Production (Docker)

### Prérequis

- Docker Engine 24+
- Docker Compose v2

### 1 — Variables d'environnement

Créer un fichier `.env` à la racine du projet (à côté de `compose.yaml`) :

```properties
DATABASE_NAME=parraindex
DATABASE_USER=app
DATABASE_PASSWORD=mot_de_passe_securise
APP_SECRET=une_chaine_aleatoire_longue_32_chars
MAIL_WEB_PORT=8025   # Port de l'interface web Mailpit
MAIL_SMTP_PORT=1025  # Port SMTP exposé par Mailpit
```

### 2 — Démarrer les services

```bash
docker compose up --build -d
```

Les services démarrés :

| Service    | Image                          | Port exposé                                      |
| ---------- | ------------------------------ | ------------------------------------------------ |
| `app`      | build local (FrankenPHP/Caddy) | 80                                               |
| `database` | mysql:9                        | 3306                                             |
| `mail`     | axllent/mailpit                | `MAIL_WEB_PORT` (8025) / `MAIL_SMTP_PORT` (1025) |

### 3 — Initialiser la base de données (premier démarrage)

```bash
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

### 4 — Accéder à l'application

- Application : `http://localhost`
- Interface mail (Mailpit) : `http://localhost:8025`

### Arrêter les services

```bash
docker compose down          # Arrêter sans supprimer les volumes
docker compose down -v       # Arrêter et supprimer les données
```

---

## Contribuer

Les contributions sont les bienvenues. Merci de lire [CONTRIBUTING.md](CONTRIBUTING.md) avant d'ouvrir une pull request.

## Contributors

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tbody>
    <tr>
      <td align="center"><a href="https://lukamaret.com"><img src="https://avatars.githubusercontent.com/u/48085295?v=4?s=100" width="100px;" alt="Luka Maret"/><br /><sub><b>Luka Maret</b></sub></a><br /><a href="https://github.com/LukaMrt/Parraindex/commits?author=LukaMrt" title="Code">💻</a> <a href="#infra-LukaMrt" title="Infrastructure (Hosting, Build-Tools, etc)">🚇</a> <a href="#projectManagement-LukaMrt" title="Project Management">📆</a> <a href="https://github.com/LukaMrt/Parraindex/commits?author=LukaMrt" title="Tests">⚠️</a> <a href="#" title="Documentation">📖</a></td>
      <td align="center"><a href="https://irophin.github.io/CV-Web/"><img src="https://avatars.githubusercontent.com/u/62310861?v=4?s=100" width="100px;" alt="Lilian Baudry"/><br /><sub><b>Lilian Baudry</b></sub></a><br /><a href="https://github.com/LukaMrt/Parraindex/commits?author=Irophin" title="Code">💻</a> <a href="#" title="Review">👀</a> <a href="#" title="Ideas">🤔</a> <a href="#" title="Design">🎨</a></td>
      <td align="center"><a href="https://github.com/Melvyn27"><img src="https://avatars.githubusercontent.com/u/93776074?v=4?s=100" width="100px;" alt="Melvyn Delpree"/><br /><sub><b>Melvyn Delpree</b></sub></a><br /><a href="https://github.com/LukaMrt/Parraindex/commits?author=Melvyn27" title="Code">💻</a> <a href="#" title="Design">🎨</a> <a href="#" title="Documentation">📖</a></td>
      <td align="center"><a href="https://github.com/415K7467"><img src="https://avatars.githubusercontent.com/u/93972726?v=4?s=100" width="100px;" alt="Vincent Chavot-Dambrun"/><br /><sub><b>Vincent Chavot-Dambrun</b></sub></a><br /><a href="https://github.com/LukaMrt/Parraindex/commits?author=415K7467" title="Code">💻</a></td>
      <td align="center"><a href="https://github.com/dvachette"><img src="https://github.com/dvachette.png?s=100" width="100px;" alt="dvachette"/><br /><sub><b>dvachette</b></sub></a><br /><a href="https://github.com/LukaMrt/Parraindex/commits?author=dvachette" title="Code">💻</a></td>
    </tr>
  </tbody>
</table>
<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->
<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!

## Licence

Parraindex est distribué sous la [licence MIT](LICENSE).
