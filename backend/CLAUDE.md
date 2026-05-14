# CLAUDE.md - Guide pour Assistants IA

## Vue d'ensemble du projet

**Parraindex** est une application web Symfony 7.2 qui gère les relations de parrainage (parrain/filleul) entre étudiants de l'IUT Lyon 1. C'est un réseau social pour visualiser et créer des liens entre personnes.

### Stack technique
- **Backend**: PHP 8.4+, Symfony 7.2, Doctrine ORM 3.3
- **Frontend**: Twig (SSR), Vanilla JavaScript (ES6 modules), SCSS
- **Base de données**: MySQL 9.1 / MariaDB
- **Déploiement**: Docker avec FrankenPHP

## Structure du projet

```
/home/user/Parraindex/
├── src/
│   ├── Controller/          # 11 contrôleurs (routes HTTP)
│   ├── Entity/              # 11 entités Doctrine
│   │   ├── Person/          # User, Person, Role, ResetPasswordRequest
│   │   ├── Sponsor/         # Relations de parrainage
│   │   ├── Contact/         # Formulaire de contact
│   │   └── Characteristic/  # Caractéristiques des personnes
│   ├── Repository/          # 7 repositories (accès données)
│   ├── Form/                # 7 types de formulaires
│   ├── Security/            # Voters, EmailVerifier, LoginListener
│   └── Fixture/             # Données de test
├── templates/               # Templates Twig (~35 fichiers)
│   ├── layouts/             # Templates de base
│   └── components/          # Composants réutilisables
├── assets/
│   ├── styles/              # SCSS (60+ fichiers)
│   └── scripts/             # JavaScript (18 modules)
├── config/                  # Configuration Symfony
├── migrations/              # Migrations Doctrine
└── tests/                   # Tests PHPUnit
```

## Commandes essentielles

```bash
# Serveur de développement
composer server-start        # Démarrer
composer server-stop         # Arrêter

# Base de données
composer migration           # Exécuter les migrations
composer migration-diff      # Créer une nouvelle migration
composer fixtures            # Charger les données de test

# Qualité du code
composer phpstan             # Analyse statique (niveau max)
composer phpcs               # Vérification style de code
composer phpcs-fix           # Correction automatique
composer rector              # Modernisation du code
composer rector:dry          # Prévisualisation rector

# Tests
composer test                # Lancer les tests PHPUnit

# Assets
composer scss:build          # Compiler SCSS et assets
```

## Architecture du code

### Contrôleurs principaux

| Contrôleur | Route | Rôle |
|------------|-------|------|
| `HomeController` | `/` | Page d'accueil |
| `PersonController` | `/personne/{id}` | Profils et édition |
| `SponsorController` | `/parrainage/{id}` | Gestion des parrainages |
| `AdminController` | `/admin/*` | Administration (ROLE_ADMIN) |
| `SecurityController` | `/login`, `/register` | Authentification |
| `ContactController` | `/contact` | Formulaire de contact |
| `TreeController` | `/tree` | Arbre des parrainages |
| `DataController` | `/data/download/{id}` | Export RGPD |

### Entités clés

**Person** - Profil individuel
- `firstName`, `lastName` (auto-formatés)
- `startYear` (année d'entrée)
- `picture`, `biography`, `description`, `color`
- Relations: `godFathers`, `godChildren`, `characteristics`

**User** - Compte utilisateur
- Email universitaire (regex: `^[a-zA-Z-]+\.[a-zA-Z-]+@etu\.univ-lyon1\.fr$`)
- Roles: `ROLE_USER`, `ROLE_ADMIN`
- Lié à une `Person`

**Sponsor** - Relation de parrainage
- `godFather` → `godChild`
- `type`: HEART (0), CLASSIC (1), UNKNOWN (2), FALUCHE (3)

**Contact** - Demande utilisateur
- Types: ADD_PERSON, UPDATE_PERSON, REMOVE_PERSON, ADD_SPONSOR, etc.
- Validation conditionnelle selon le type

### Sécurité

**Authentification**
- Form login avec CSRF
- Emails universitaires uniquement
- Vérification email obligatoire
- Reset password avec tokens

**Autorisation (Voters)**
- `PersonVoter`: Édition de son propre profil uniquement
- `SponsorVoter`: Modification par parrain ou filleul
- `AdminVoter`: Accès admin (ROLE_ADMIN)

## Conventions de code

### PHP
- `declare(strict_types=1)` obligatoire
- Typage fort partout
- Attributs PHP 8 pour routing et ORM
- Fluent setters (return `static`)

### Repositories
```php
public function create(Entity $entity): void
public function update(Entity $entity): void  // Gère timestamps
public function delete(Entity $entity): void
```

### Formulaires
- Composants Twig réutilisables dans `templates/components/form/`
- Validation via attributs `#[Assert\...]` sur entités

### JavaScript
- Modules ES6 avec importmap (pas de Node.js/npm)
- Pattern: objets `field`, `preview`, `action` pour références DOM
- Fetch API pour requêtes asynchrones

### SCSS
- Architecture SMACSS/BEM
- Variables CSS dans `:root` (`--dark-blue`, `--light-grey`, etc.)
- Mixins: `flex()`, `breakpoint()`
- Breakpoints: `small` (700px), `medium` (900px), `large` (1300px)

## Patterns à respecter

### Ajout d'une nouvelle entité
1. Créer l'entité dans `src/Entity/`
2. Créer le repository dans `src/Repository/`
3. Générer la migration: `composer migration-diff`
4. Créer le fixture si nécessaire dans `src/Fixture/`

### Ajout d'une nouvelle route
1. Ajouter la méthode dans le contrôleur approprié
2. Utiliser les attributs `#[Route]` et `#[IsGranted]`
3. Créer le template Twig correspondant
4. Ajouter le style SCSS si nécessaire
5. Ajouter le JS si nécessaire et l'enregistrer dans `importmap.php`

### Modification d'une entité existante
1. Modifier l'entité
2. Créer une migration: `composer migration-diff`
3. Vérifier avec PHPStan: `composer phpstan`
4. Mettre à jour les fixtures si nécessaire

## Points d'attention

### Sécurité
- Ne jamais exposer de données personnelles sans vérification du voter
- Toujours valider les entrées utilisateur
- Utiliser les tokens CSRF sur tous les formulaires
- Les routes `/admin/*` sont protégées par `ROLE_ADMIN`

### Performance
- Utiliser le lazy loading Doctrine
- Éviter les requêtes N+1 (utiliser JOIN FETCH)
- Les assets sont compilés en production

### Qualité
- PHPStan niveau max obligatoire
- Code style PSR-12 (PHP CodeSniffer)
- Tests dans `tests/`

## Configuration environnement

### Variables d'environnement clés (.env)
```
DATABASE_URL=mysql://user:pass@host:3306/parraindex
MAILER_DSN=smtp://mailhog:1025
```

### Docker (développement)
```yaml
services:
  mysql:     # Port 3306
  mailhog:   # SMTP 1025, Web UI 8025
```

## Ressources

- **Documentation Symfony**: https://symfony.com/doc/7.2/
- **Doctrine ORM**: https://www.doctrine-project.org/projects/orm.html
- **PHPStan**: https://phpstan.org/
