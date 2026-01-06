# Plan d'Amélioration - Sécurité

## Objectif

Corriger les vulnérabilités identifiées et renforcer la posture de sécurité de l'application.

---

## Phase 1: Corrections Critiques (Priorité P0)

### 1.1 Suppression du stockage de mot de passe en clair

**Fichier**: `src/Entity/Contact/Contact.php`

**Problème**: Le champ `password` stocke le mot de passe en clair.

**Solution**:

1. Supprimer le champ `password` de l'entité Contact
2. Créer un mécanisme de token temporaire sécurisé
3. Envoyer un lien de création de compte par email

**Alternative**: Si le flux actuel doit être conservé:

1. Hasher le mot de passe avant stockage
2. Supprimer immédiatement après utilisation

```php
// Dans AdminController::resolve(), cas PASSWORD
$hashedPassword = $this->userPasswordHasher->hashPassword($user, $contact->getPassword());
$user->setPassword($hashedPassword);
// Supprimer le contact après traitement
$this->contactRepository->delete($contact);
```

### 1.2 Protection contre l'injection SQL dans orderBy

**Fichier**: `src/Repository/PersonRepository.php:26-33`

**Problème**: Le paramètre `$orderBy` est concaténé sans validation.

**Solution**:

```php
private const ALLOWED_ORDER_FIELDS = ['id', 'firstName', 'lastName', 'startYear', 'createdAt'];

public function getAll(string $orderBy = 'id'): array
{
    if (!in_array($orderBy, self::ALLOWED_ORDER_FIELDS, true)) {
        throw new \InvalidArgumentException("Invalid order field: $orderBy");
    }

    return $this->createQueryBuilder('p')
        ->orderBy('p.' . $orderBy, 'ASC')
        ->getQuery()
        ->getResult();
}
```

### 1.3 Validation côté serveur des uploads

**Fichier**: `src/Controller/PersonController.php` ou nouveau service

**Solution**: Créer un service de validation d'images:

```php
// src/Service/ImageValidator.php
class ImageValidator
{
    private const MAX_SIZE = 5 * 1024 * 1024; // 5 Mo
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    public function validate(UploadedFile $file): void
    {
        if ($file->getSize() > self::MAX_SIZE) {
            throw new \InvalidArgumentException('Image trop volumineuse (max 5 Mo)');
        }

        if (!in_array($file->getMimeType(), self::ALLOWED_TYPES, true)) {
            throw new \InvalidArgumentException('Format d\'image non supporté');
        }

        // Vérifier que c'est vraiment une image
        $imageInfo = getimagesize($file->getPathname());
        if ($imageInfo === false) {
            throw new \InvalidArgumentException('Fichier corrompu ou non valide');
        }
    }
}
```

---

## Phase 2: Corrections Importantes (Priorité P1)

### 2.1 Correction du parsing d'email

**Fichier**: `src/Controller/SecurityController.php:100-104`

**Solution**:

```php
private function extractNamesFromEmail(string $email): ?array
{
    if (!preg_match('/^([a-zA-Z-]+)\.([a-zA-Z-]+)@etu\.univ-lyon1\.fr$/', $email, $matches)) {
        return null;
    }

    return [
        'firstName' => ucfirst(strtolower($matches[1])),
        'lastName' => ucfirst(strtolower($matches[2])),
    ];
}

// Utilisation
$names = $this->extractNamesFromEmail($email);
if ($names === null) {
    $this->addFlash('error', 'Format d\'email invalide');
    return $this->redirectToRoute('register');
}
```

### 2.2 Correction du parsing de noms dans ContactType

**Fichier**: `src/Form/ContactType.php:75-78`

**Solution**:

```php
private function splitFullName(string $fullName): array
{
    $parts = explode(' ', trim($fullName), 2);

    return [
        'firstName' => $parts[0] ?? '',
        'lastName' => $parts[1] ?? '',
    ];
}

// Dans preSubmit
if (in_array(Type::from(intval($data['type'])), $typesWithRelatedPerson)) {
    $names = $this->splitFullName($data['relatedPerson'] ?? '');
    if (empty($names['firstName']) || empty($names['lastName'])) {
        // Gérer l'erreur
    }
    $data['relatedPersonFirstName'] = $names['firstName'];
    $data['relatedPersonLastName'] = $names['lastName'];
    unset($data['relatedPerson']);
}
```

### 2.3 Utiliser DELETE/POST au lieu de GET pour les actions destructives

**Fichier**: `src/Controller/AdminController.php:52-59`

**Problème actuel**:

```php
#[Route('/admin/contact/{id}/delete', methods: [Request::METHOD_GET])]
```

**Solution**:

```php
#[Route('/admin/contact/{id}/delete', methods: [Request::METHOD_POST, Request::METHOD_DELETE])]
#[IsCsrfTokenValid('delete_contact', tokenKey: '_token')]
public function delete(Contact $contact): Response
```

Et dans le template:

```twig
<form action="{{ path('admin_contact_delete', {id: contact.id}) }}" method="POST">
    <input type="hidden" name="_token" value="{{ csrf_token('delete_contact') }}">
    <button type="submit">Supprimer</button>
</form>
```

---

## Phase 3: Renforcement (Priorité P2)

### 3.1 Protection XSS avec Symfony HTML Sanitizer

Utiliser le composant HTML Sanitizer de Symfony pour nettoyer les contenus HTML des champs texte libres.

**Installation**:

```bash
composer require symfony/html-sanitizer
```

**Configuration** (`config/packages/html_sanitizer.yaml`):

```yaml
framework:
    html_sanitizer:
        sanitizers:
            app.biography_sanitizer:
                # Autoriser uniquement le texte simple, pas de HTML
                allow_safe_elements: false
            app.description_sanitizer:
                # Autoriser quelques balises basiques
                allow_elements:
                    p: [ ]
                    br: [ ]
                    strong: [ ]
                    em: [ ]
                    ul: [ ]
                    ol: [ ]
                    li: [ ]
```

**Utilisation dans un service ou contrôleur**:

```php
// src/Service/ContentSanitizer.php
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class ContentSanitizer
{
    public function __construct(
        private readonly HtmlSanitizerInterface $biographySanitizer,
        private readonly HtmlSanitizerInterface $descriptionSanitizer,
    ) {}

    public function sanitizeBiography(?string $content): ?string
    {
        if ($content === null) {
            return null;
        }

        return $this->biographySanitizer->sanitize($content);
    }

    public function sanitizeDescription(?string $content): ?string
    {
        if ($content === null) {
            return null;
        }

        return $this->descriptionSanitizer->sanitize($content);
    }
}
```

**Utilisation dans le repository avant persistence**:

```php
// src/Repository/PersonRepository.php
public function __construct(
    ManagerRegistry $managerRegistry,
    private readonly ContentSanitizer $contentSanitizer,
) {
    parent::__construct($managerRegistry, Person::class);
}

public function update(Person $person): void
{
    // Sanitizer les champs texte avant persistence
    $person->setBiography($this->contentSanitizer->sanitizeBiography($person->getBiography()));
    $person->setDescription($this->contentSanitizer->sanitizeDescription($person->getDescription()));

    if (!$person->getCreatedAt() instanceof \DateTimeInterface) {
        $person->setCreatedAt(new \DateTimeImmutable());
    }

    if ($person->getPicture() === null) {
        $person->setPicture(self::DEFAULT_PICTURE);
    }

    $this->getEntityManager()->persist($person);
    $this->getEntityManager()->flush();
}
```

**Alternative avec filtre Twig** (pour l'affichage):

```twig
{# Sanitizer à l'affichage plutôt qu'au stockage #}
{{ person.biography|sanitize_html('app.biography_sanitizer') }}
```

---

## Checklist de Sécurité

### Avant déploiement

- [ ] Faille mot de passe en clair corrigée
- [ ] Injection SQL corrigée
- [ ] Validation upload serveur ajoutée
- [ ] CSRF sur toutes les actions sensibles
- [ ] HTML Sanitizer configuré

### Régulièrement

- [ ] Mise à jour des dépendances (`composer update`)
- [ ] Audit des dépendances (`composer audit`)
- [ ] Revue des logs d'accès
- [ ] Test de pénétration

### Configuration production

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=prod`
- [ ] Clé secrète unique (`APP_SECRET`)
- [ ] HTTPS forcé
- [ ] Cookies secure et httponly

---

## Ressources

- [OWASP Top 10](https://owasp.org/Top10/)
- [Symfony Security](https://symfony.com/doc/current/security.html)
- [Symfony HTML Sanitizer](https://symfony.com/doc/current/html_sanitizer.html)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
