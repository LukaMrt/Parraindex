# Plan de migration — Gestion des images de profil

**Stack cible :** VichUploaderBundle + contrainte `Assert\Image` Symfony + LiipImagineBundle (miniatures)  
**Effort total estimé :** ~6h  
**Date de rédaction :** 2026-05-11

---

## Contexte

L'implémentation actuelle stocke les images via un `file->move()` brut dans le contrôleur, sans validation du type MIME réel, sans limite de taille, sans nettoyage des anciennes images et sans miniatures. Ce plan décrit la migration vers une stack standard Symfony.

### Ce qui change

| Aspect           | Avant                        | Après                                     |
| ---------------- | ---------------------------- | ----------------------------------------- |
| Logique d'upload | Dans `PersonApiController`   | Dans `PictureService` + VichUploader      |
| Validation       | `guessExtension()` seulement | `Assert\Image` (MIME, taille, dimensions) |
| Nommage          | `{id}_{uniqid()}.{ext}`      | Hash SHA-256 du contenu                   |
| Nettoyage        | Jamais                       | Automatique via événements VichUploader   |
| Miniatures       | Aucune                       | LiipImagineBundle (filtre `avatar_thumb`) |
| Dossier          | `public/uploads/pictures/`   | `public/uploads/avatars/`                 |
| Champ BDD        | `VARCHAR(255) picture`       | `VARCHAR(255) picture` (inchangé)         |

---

## Étape 1 — Installation des dépendances

```bash
cd backend
composer require vich/uploader-bundle
composer require liip/imagine-bundle
```

> LiipImagineBundle nécessite l'extension PHP `gd` ou `imagick`. Vérifier la présence dans le `Dockerfile`.

**Dockerfile** — ajouter si absent :
```dockerfile
RUN install-php-extensions gd
```

---

## Étape 2 — Configuration VichUploaderBundle

Créer `backend/config/packages/vich_uploader.yaml` :

```yaml
vich_uploader:
    db_driver: orm
    mappings:
        person_avatar:
            uri_prefix: /uploads/avatars
            upload_destination: '%kernel.project_dir%/public/uploads/avatars'
            namer:
                service: Vich\UploaderBundle\Naming\HashNamer
                options:
                    algorithm: sha256
                    length: 40
            delete_on_update: true   # supprime l'ancienne image automatiquement
            delete_on_remove: true   # supprime quand la Person est supprimée
            inject_on_load: false    # pas besoin de réhydrater le File au chargement
```

> `HashNamer` avec SHA-256 garantit l'unicité et évite les collisions que `uniqid()` peut produire en environnement concurrent. Il déduplique aussi naturellement les fichiers identiques.

---

## Étape 3 — Configuration LiipImagineBundle

Créer `backend/config/packages/liip_imagine.yaml` :

```yaml
liip_imagine:
    driver: gd
    resolvers:
        default:
            web_path:
                web_root: '%kernel.project_dir%/public'
                cache_prefix: 'media/cache'
    loaders:
        default:
            filesystem:
                data_root: '%kernel.project_dir%/public'
    filter_sets:
        avatar_thumb:
            quality: 85
            filters:
                thumbnail:
                    size: [200, 200]
                    mode: outbound   # crop centré (pas de déformation)
                strip:              # supprime les métadonnées EXIF (confidentialité)
                    ~
        avatar_full:
            quality: 90
            filters:
                thumbnail:
                    size: [400, 400]
                    mode: inset
                strip:
                    ~
```

Activer les routes dans `backend/config/routes.yaml` :

```yaml
_liip_imagine:
    resource: '@LiipImagineBundle/Resources/config/routing.xml'
```

> Le filtre `strip` supprime les métadonnées EXIF (GPS, appareil photo) — important pour la vie privée des étudiants.

---

## Étape 4 — Modification de l'entité `Person`

Fichier : `backend/src/Entity/Person/Person.php`

Ajouter les imports :

```php
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;
```

Annoter la classe :

```php
#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[UniqueEntity(fields: ['firstName', 'lastName'], message: 'person.unique')]
#[Vich\Uploadable]
class Person implements \Stringable
```

Remplacer le champ `picture` existant par deux champs :

```php
// Champ persisté en BDD (chemin du fichier, inchangé)
#[ORM\Column(length: 255, nullable: true)]
private ?string $picture = null;

// Champ transient (File PHP, non persisté, utilisé uniquement pour l'upload)
#[Vich\UploadableField(mapping: 'person_avatar', fileNameProperty: 'picture')]
#[Assert\Image(
    maxSize: '5M',
    mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    maxWidth: 4096,
    maxHeight: 4096,
    detectCorrupted: true,
)]
private ?File $pictureFile = null;
```

Ajouter les getters/setters pour `pictureFile` :

```php
public function getPictureFile(): ?File
{
    return $this->pictureFile;
}

public function setPictureFile(?File $pictureFile): static
{
    $this->pictureFile = $pictureFile;

    // VichUploader détecte un changement via updatedAt.
    // Il faut donc mettre à jour un champ date pour déclencher le flush Doctrine.
    if ($pictureFile !== null) {
        $this->updatedAt = new \DateTimeImmutable();
    }

    return $this;
}
```

> **Attention :** VichUploader déclenche l'upload réel lors des événements Doctrine (`prePersist` / `preUpdate`). Il a besoin d'un changement détectable pour déclencher le flush — d'où la mise à jour de `updatedAt`. Si l'entité n'a pas encore ce champ, l'ajouter (voir étape 5).

---

## Étape 5 — Ajout du champ `updatedAt` (si absent)

Vérifier si `Person` a déjà un champ `updatedAt`. Si non, l'ajouter :

```php
#[ORM\Column(nullable: true)]
private ?\DateTimeImmutable $updatedAt = null;

public function getUpdatedAt(): ?\DateTimeImmutable
{
    return $this->updatedAt;
}
```

Puis générer la migration :

```bash
cd backend
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

---

## Étape 6 — Refactoring du contrôleur

Supprimer toute la logique brute dans `PersonApiController::uploadPicture()` et déléguer à VichUploader via l'entité.

**Avant** (lignes 102–132 de `PersonApiController.php`) :

```php
$extension = $file->guessExtension() ?? 'bin';
$filename  = sprintf('%d_%s.%s', $person->getId(), uniqid(), $extension);
$projectDir = $this->getParameter('kernel.project_dir');
$file->move($projectDir . '/public/uploads/pictures', $filename);
$person->setPicture($filename);
$this->personService->update($person);
```

**Après** :

```php
#[Route('/api/persons/{id}/picture', name: 'api_persons_picture', methods: ['POST'])]
#[IsGranted(PersonVoter::EDIT, subject: 'person')]
public function uploadPicture(Person $person, Request $request): JsonResponse
{
    $file = $request->files->get('picture');

    if (!$file instanceof UploadedFile) {
        return ApiResponse::error(
            new ApiError(ErrorCode::VALIDATION_ERROR, 'Aucun fichier envoyé'),
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }

    $person->setPictureFile($file);

    $errors = $this->validator->validate($person, groups: ['Default']);
    if (count($errors) > 0) {
        return ApiResponse::error(
            new ApiError(ErrorCode::VALIDATION_ERROR, (string) $errors->get(0)->getMessage()),
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }

    $this->personService->update($person);

    return ApiResponse::success(['picture' => $person->getPicture()]);
}
```

Injecter le `ValidatorInterface` dans le constructeur du contrôleur :

```php
use Symfony\Component\Validator\Validator\ValidatorInterface;

public function __construct(
    // ...dépendances existantes...
    private readonly ValidatorInterface $validator,
) {}
```

---

## Étape 7 — Mise à jour du frontend

### 7a. URL des images

Modifier `frontend/src/lib/imageUrl.ts` pour pointer vers le nouveau dossier et supporter les miniatures :

```typescript
const FALLBACK_AVATAR = '/images/icons/logo-blue.svg';

export function pictureUrl(picture: string | null, size: 'thumb' | 'full' = 'thumb'): string {
  if (picture === null) return FALLBACK_AVATAR;
  const filter = size === 'thumb' ? 'avatar_thumb' : 'avatar_full';
  return `/media/cache/${filter}/uploads/avatars/${picture}`;
}
```

> Les URLs LiipImagine suivent le pattern `/media/cache/{filter}/{chemin_original}`. La miniature est générée à la première requête puis mise en cache sur disque.

### 7b. Usages dans les composants

Tous les appels existants `pictureUrl(person.picture)` continuent de fonctionner sans modification (la signature est rétrocompatible avec la valeur par défaut `'thumb'`).

Pour les pages de profil complet où la résolution maximale est souhaitable :

```tsx
<img src={pictureUrl(person.picture, 'full')} alt={person.fullName} />
```

### 7c. Migration des fichiers existants

Les anciennes images dans `public/uploads/pictures/` restent accessibles via leurs anciennes URLs. Elles ne seront pas supprimées automatiquement. Deux options :

- **Option simple** : laisser coexister les deux dossiers, les anciennes URLs restent valides jusqu'à la prochaine modification de chaque profil.
- **Option propre** : script de migration one-shot qui déplace les fichiers et met à jour les noms en BDD (voir Étape 8).

---

## Étape 8 — Script de migration des données existantes (optionnel)

Si les anciens fichiers doivent être déplacés proprement :

```php
// backend/src/Command/MigratePicturesCommand.php
#[AsCommand(name: 'app:migrate-pictures')]
class MigratePicturesCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $persons = $this->personRepository->findAll();
        $oldDir  = $this->projectDir . '/public/uploads/pictures/';
        $newDir  = $this->projectDir . '/public/uploads/avatars/';

        foreach ($persons as $person) {
            $oldName = $person->getPicture();
            if ($oldName === null) continue;

            $oldPath = $oldDir . $oldName;
            if (!file_exists($oldPath)) continue;

            $hash    = hash_file('sha256', $oldPath);
            $ext     = pathinfo($oldName, PATHINFO_EXTENSION);
            $newName = substr($hash, 0, 40) . '.' . $ext;

            copy($oldPath, $newDir . $newName);
            $person->setPicture($newName);
            $this->personRepository->update($person);

            $output->writeln("Migré : {$oldName} → {$newName}");
        }

        return Command::SUCCESS;
    }
}
```

Exécution :

```bash
php bin/console app:migrate-pictures
```

---

## Étape 9 — Tests à écrire

Fichier : `backend/tests/Functional/Api/PersonApiControllerTest.php`

Cas à couvrir :

```php
// Cas passants
testUploadPictureAsOwnerReturnsUpdatedFilename()
testUploadPictureReplacesOldFile()              // vérifie que l'ancienne est supprimée

// Cas bloquants
testUploadPictureAsNonOwnerReturnsForbidden()
testUploadPictureUnauthenticatedReturnsUnauthorized()
testUploadPictureWithoutFilesReturnsValidationError()
testUploadPictureWithInvalidMimeTypeReturnsValidationError()  // ex: text/plain
testUploadPictureExceedingMaxSizeReturnsValidationError()     // ex: fichier > 5 Mo
testUploadPictureThatIsNotAnImageReturnsValidationError()     // ex: ZIP renommé en .jpg
```

---

## Étape 10 — Nettoyage

Une fois la migration validée :

1. Supprimer le code brut de `PersonApiController::uploadPicture()` (remplacé à l'étape 6).
2. Supprimer les imports `kernel.project_dir` et `uniqid()` du contrôleur.
3. Vider `public/uploads/pictures/` si le script de migration a été exécuté.
4. Mettre à jour `backend/.gitignore` pour ignorer les deux dossiers d'uploads :

```gitignore
/public/uploads/
/public/media/cache/
```

---

## Récapitulatif des fichiers modifiés

| Fichier                                                    | Action                                                 |
| ---------------------------------------------------------- | ------------------------------------------------------ |
| `backend/composer.json`                                    | `vich/uploader-bundle`, `liip/imagine-bundle`          |
| `backend/config/packages/vich_uploader.yaml`               | Nouveau                                                |
| `backend/config/packages/liip_imagine.yaml`                | Nouveau                                                |
| `backend/config/routes.yaml`                               | Ajout routes LiipImagine                               |
| `backend/src/Entity/Person/Person.php`                     | `#[Vich\Uploadable]`, champ `pictureFile`, `updatedAt` |
| `backend/src/Controller/Api/PersonApiController.php`       | Simplification `uploadPicture()`                       |
| `backend/src/Command/MigratePicturesCommand.php`           | Nouveau (optionnel)                                    |
| `backend/tests/Functional/Api/PersonApiControllerTest.php` | Nouveaux cas de test                                   |
| `frontend/src/lib/imageUrl.ts`                             | Support miniatures LiipImagine                         |
| `backend/.gitignore`                                       | Ignorer `public/uploads/` et `public/media/cache/`     |

---

## Ordre d'exécution recommandé

```
1. composer require (étapes 1)
2. Fichiers de config YAML (étapes 2–3)
3. Modifier Person.php (étape 4–5) + migration Doctrine
4. Refactorer le contrôleur (étape 6)
5. Mettre à jour le frontend (étape 7)
6. Écrire les tests (étape 9)
7. Valider en dev + script de migration si besoin (étape 8)
8. Nettoyage (étape 10)
```

---

## Références

- [VichUploaderBundle — Documentation](https://github.com/dustin10/VichUploaderBundle/blob/master/docs/index.md)
- [LiipImagineBundle — Documentation](https://symfony.com/bundles/LiipImagineBundle/current/index.html)
- [Assert\Image — Symfony Validator](https://symfony.com/doc/current/reference/constraints/Image.html)
- [HashNamer — VichUploader](https://github.com/dustin10/VichUploaderBundle/blob/master/docs/namers.md)
