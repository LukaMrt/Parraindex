# Backend — Parraindex

API JSON Symfony + back-office EasyAdmin. Consommé par la SPA React (`../frontend`).

## Stack
- PHP 8.5 (`declare(strict_types=1)` partout), Symfony 8, Doctrine ORM 3
- EasyAdmin 5 (back-office `/admin`), Vich Uploader + Liip Imagine (images), Mailer
- Serveur prod : FrankenPHP (Caddy). Tests : PHPUnit + dama/doctrine-test-bundle

## Architecture en couches
Le flux est strict : **Controller → Service → Repository**. Les controllers n'orchestrent que le HTTP, toute la logique métier est dans les services.

```
src/
  Api/             # Enveloppe JSON : ApiResponse, ApiError, ErrorCode
  Controller/Api/  # Endpoints JSON (préfixe /api) — orchestration uniquement
  Controller/Admin/# Back-office EasyAdmin (CRUD, dashboard, import CSV, fusion)
  Command/         # Commandes console (DeployReset, ResetPassword)
  Dto/             # DTOs requête (#[MapRequestPayload]) et réponse (mapping explicite)
  Entity/          # Entités Doctrine (Person/, Sponsor/, Contact/, Characteristic/)
  EventListener/   # Listeners Doctrine (nettoyage des logos école/asso)
  Fixture/         # Données de démonstration / base des tests
  Form/            # Types de formulaires (back-office uniquement)
  Repository/      # Accès données — méthodes create/update/delete + requêtes
  Security/        # Voters + handlers d'auth API (success/failure/logout)
  Service/         # Logique métier ; Service/Contact/ = pattern Resolver
```

## Convention API
Toutes les réponses passent par `App\Api\ApiResponse` :
- Succès : `{ "data": ... }` → `ApiResponse::success($dto)`
- Erreur : `{ "error": { "code", "message", "violations" } }` → `ApiResponse::error()`, `::notFound()`, `::validationError()`…

Les codes d'erreur sont l'enum `ErrorCode` (miroir exact du type `ErrorCode` côté frontend — garder les deux synchronisés).

Pattern d'un endpoint :
1. DTO de requête via `#[MapRequestPayload]` (validation auto par contraintes Symfony)
2. Autorisation par `#[IsGranted(PersonVoter::EDIT, subject: 'person')]` ou `#[IsGranted(Role::ADMIN->value)]`
3. Délégation au service
4. Mapping vers un `*ResponseDto` (jamais sérialiser une entité directement) puis `ApiResponse::success(...)`

## Sécurité
- Deux firewalls : `admin` (`^/admin`) et `api` (`^/api`), tous deux stateful (`context: main`), `json_login` pour l'API.
- Accès : `^/admin` et `^/api/admin` → `ROLE_ADMIN`.
- Autorisation fine par **Voters** : `PersonVoter` (édition de son profil), `SponsorVoter`, `AdminVoter`.
- CSRF double-submit : cookie `XSRF-TOKEN` → header `X-XSRF-TOKEN` côté front.
- Emails universitaires uniquement, vérification d'email obligatoire (`symfonycasts/verify-email`).

## Pattern Resolver (contacts)
Une demande de contact (`Contact` + enum `Type`) est traitée par `ContactResolverManager`, qui délègue à un resolver dédié (`AddPersonResolver`, `AddSponsorResolver`, `RemoveSponsorResolver`, `PasswordResolver`, `DefaultResolver`…). Pour un nouveau type de demande : ajouter une valeur à l'enum `Type` et un resolver implémentant `ContactResolverInterface`.

## Commandes (scripts composer)
```bash
composer phpstan          # Analyse statique — niveau 10, zéro ignore toléré dans src/
composer phpcs            # Style (PSR-12 + phpcs.xml) ; composer phpcs:fix pour corriger
composer rector:dry       # Vérif modernisation ; composer rector pour appliquer
composer test             # PHPUnit (crée la BDD test, migre, fixtures, coverage HTML)
php vendor/bin/infection  # Tests de mutation

composer migration:new    # make:migration
composer migration:run    # appliquer les migrations
composer database:reset    # drop + create + migrate + fixtures
```

Tests organisés en `tests/Unit` (entités), `tests/Integration/Repository` (vraie BDD), `tests/Functional/Api` (endpoints).

## Conventions
- `declare(strict_types=1)`, typage fort partout, PHPStan niveau 10.
- Setters fluent (`return static`).
- Repositories exposent `create()` / `update()` (gère les timestamps) / `delete()`.
- Ne jamais exposer une entité en JSON : passer par un DTO de réponse.
- Toute modification de schéma → `composer migration:new` + commit de la migration (la CI échoue s'il reste des changements non migrés).

## Config (variables d'environnement, pas de DSN)
`DATABASE_DRIVER/HOST/PORT/USER/PASSWORD/NAME`, `MAIL_HOST/PORT/USER/PASSWORD/NAME`, `APP_SECRET`, `APP_URL`. En local : Mailpit sur `:1025` (SMTP) / `:8025` (web).

## Ressources
- Symfony 8 : https://symfony.com/doc/current/
- Doctrine ORM : https://www.doctrine-project.org/projects/orm.html
- EasyAdmin : https://symfony.com/bundles/EasyAdminBundle/current/index.html
