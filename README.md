# Parraindex

[![Version](https://img.shields.io/badge/version-2.0.0-blue)]()
[![Lines of Code](https://sonarcloud.io/api/project_badges/measure?project=LukaMrt_Parraindex&metric=ncloc)](https://sonarcloud.io/summary/new_code?id=LukaMrt_Parraindex)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=LukaMrt_Parraindex&metric=coverage)](https://sonarcloud.io/summary/new_code?id=LukaMrt_Parraindex)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=LukaMrt_Parraindex&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=LukaMrt_Parraindex)
![GitHub language count](https://img.shields.io/github/languages/count/lukamrt/parraindex)
![GitHub](https://img.shields.io/github/license/lukamrt/parraindex)
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-4-orange.svg?style=flat)](#contributors)
<!-- ALL-CONTRIBUTORS-BADGE:END -->

## À propos

Parraindex est une application web qui visualise les relations de parrainage entre étudiants de l'IUT Lyon 1. Chaque étudiant peut créer son profil, voir les liens qui existent entre les personnes, et soumettre des demandes de parrainage validées par les administrateurs.

Le site est accessible à l'adresse : [https://parraindex.com](https://parraindex.com)

## Architecture

L'application suit une architecture **SPA découplée** :

```
parraindex/
├── backend/       # API Symfony 8 — FrankenPHP
├── frontend/      # SPA React 19 — Vite
├── docker/        # Config Nginx (prod)
└── compose.yaml   # Orchestration Docker (prod)
```

**Backend** — `backend/`

| Technologie  | Version | Rôle                            |
| ------------ | ------- | ------------------------------- |
| PHP          | 8.5     | Runtime                         |
| Symfony      | 8       | Framework HTTP + DI             |
| FrankenPHP   | 1.5     | Serveur HTTP (remplace PHP-FPM) |
| Doctrine ORM | 3       | Accès base de données           |
| MySQL        | 9       | Base de données                 |

Structure interne du backend :
```
backend/src/
  Api/          # Wrappers de réponse JSON (ApiResponse, ApiError, ErrorCode)
  Controller/   # Controllers API (orchestration uniquement, pas de logique métier)
  Dto/          # DTOs de requête et réponse (Person, Sponsor, Contact, Auth…)
  Entity/       # Entités Doctrine
  Repository/   # Accès aux données
  Security/     # Voters (PersonVoter, AdminVoter…)
  Service/      # Logique métier (PersonService, SponsorService…)
```

**Frontend** — `frontend/`

| Technologie  | Version  | Rôle        |
| ------------ | -------- | ----------- |
| React        | 19       | UI          |
| Vite         | 6        | Bundler     |
| TypeScript   | 5 strict | Typage      |
| Tailwind CSS | 4.2      | Styles      |
| React Router | 7        | Routing SPA |
| Vitest       | latest   | Tests       |

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

**En production**, Nginx sert les fichiers statiques Vite et fait office de reverse proxy :
```
Nginx :80
  ├── /api/*  →  FrankenPHP (Symfony)
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

Créer le fichier `.env.local` (jamais commité) :

```properties
APP_ENV=dev
APP_SECRET=une_chaine_aleatoire_longue

DATABASE_URL="mysql://user:password@127.0.0.1:3306/parraindex"

MAILER_DSN="smtp://localhost:1025"   # Mailhog en local, ou null://null pour ignorer
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

Créer le fichier `.env.local` pour pointer vers le backend local :

```properties
VITE_API_BASE_URL=http://localhost:8000
```

Démarrer le serveur de développement :

```bash
npm run dev
```

Le frontend répond sur `http://localhost:5173` avec hot-reload.

### Commandes utiles (développement)

**Backend :**

```bash
# Qualité
composer phpstan          # Analyse statique niveau max
composer phpcs            # Style de code
composer test             # Tests PHPUnit
composer infection        # Tests de mutation

# Base de données
php bin/console doctrine:migrations:migrate
php bin/console doctrine:migrations:diff   # Générer une migration
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
MAIL_WEB_PORT=8025   # Port exposé pour l'interface Mailhog
```

### 2 — Démarrer les services

```bash
docker compose up --build -d
```

Les services démarrés :

| Service      | Image                  | Port exposé                     |
| ------------ | ---------------------- | ------------------------------- |
| `nginx`      | build local            | 80                              |
| `frankenphp` | build local (backend/) | —                               |
| `database`   | mysql:9                | —                               |
| `mail`       | mailhog/mailhog        | `MAIL_WEB_PORT` (défaut : 8025) |

### 3 — Initialiser la base de données (premier démarrage)

```bash
docker compose exec frankenphp php bin/console doctrine:migrations:migrate --no-interaction
```

### 4 — Accéder à l'application

- Application : `http://localhost`
- Interface mail (Mailhog) : `http://localhost:8025`

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
    </tr>
  </tbody>
</table>
<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->
<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!

## Licence

Parraindex est distribué sous la [licence MIT](LICENSE).
