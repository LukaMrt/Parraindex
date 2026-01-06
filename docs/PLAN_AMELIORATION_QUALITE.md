# Plan d'Amélioration - Qualité et Propreté du Code

## Objectif
Améliorer la qualité, la lisibilité et la maintenabilité du code.

---

## Phase 1: Refactoring Critique (Priorité P1)

### 1.1 Refactorisation de AdminController::resolve()

**Problème**: Méthode de 140+ lignes avec un switch complexe.

**Solution**: Extraire la logique dans des services dédiés.

**Étape 1**: Créer une interface pour les handlers

```php
// src/Service/Contact/ContactResolverInterface.php
interface ContactResolverInterface
{
    public function supports(Contact $contact): bool;
    public function resolve(Contact $contact): ?Response;
}
```

**Étape 2**: Créer les handlers spécifiques

```php
// src/Service/Contact/Resolver/AddPersonResolver.php
class AddPersonResolver implements ContactResolverInterface
{
    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {}

    public function supports(Contact $contact): bool
    {
        return $contact->getType() === Type::ADD_PERSON;
    }

    public function resolve(Contact $contact): ?Response
    {
        $person = new Person()
            ->setFirstName($contact->getRelatedPersonFirstName())
            ->setLastName($contact->getRelatedPersonLastName())
            ->setStartYear($contact->getEntryYear());

        $this->personRepository->create($person);
        return null;
    }
}
```

**Étape 3**: Créer un service agrégateur

```php
// src/Service/Contact/ContactResolverManager.php
class ContactResolverManager
{
    /** @param iterable<ContactResolverInterface> $resolvers */
    public function __construct(
        private readonly iterable $resolvers,
    ) {}

    public function resolve(Contact $contact): ?Response
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($contact)) {
                return $resolver->resolve($contact);
            }
        }

        throw new \RuntimeException('No resolver found for contact type');
    }
}
```

**Étape 4**: Configuration du service

```yaml
# config/services.yaml
services:
    _instanceof:
        App\Service\Contact\ContactResolverInterface:
            tags: ['app.contact_resolver']

    App\Service\Contact\ContactResolverManager:
        arguments:
            $resolvers: !tagged_iterator app.contact_resolver
```

**Étape 5**: Simplifier le contrôleur

```php
// src/Controller/AdminController.php
#[Route('/contact/{id}/resolve')]
public function resolve(Contact $contact): Response
{
    $response = $this->resolverManager->resolve($contact);

    if ($response !== null) {
        return $response;
    }

    $this->addFlash('success', 'Contact résolu');
    return $this->redirectToRoute('admin_contact');
}
```

### 1.2 Élimination du code mort

**Fichiers concernés**:
- `src/Controller/AdminController.php:203-204`

**Action**: Supprimer les lignes commentées:
```php
//        $contact->setResolutionDate(new \DateTime());
//        $this->contactRepository->update($contact);
```

### 1.3 Correction des suppressions PHPStan

**Fichier**: `src/Controller/SecurityController.php:82`

**Problème**:
```php
// @phpstan-ignore-next-line
$this->addFlash('error', $error->getMessage());
```

**Solution**: Typer correctement
```php
foreach ($form->getErrors(true) as $error) {
    $message = $error->getMessage();
    if (is_string($message)) {
        $this->addFlash('error', $message);
    }
}
```

---

## Phase 2: Tests (Priorité P1)

### 2.1 Structure de tests recommandée

```
tests/
├── Unit/
│   ├── Entity/
│   │   ├── PersonTest.php
│   │   ├── UserTest.php
│   │   └── SponsorTest.php
│   ├── Service/
│   │   └── Contact/
│   │       └── AddPersonResolverTest.php
│   └── Security/
│       └── Voter/
│           └── PersonVoterTest.php
├── Integration/
│   ├── Repository/
│   │   ├── PersonRepositoryTest.php
│   │   └── UserRepositoryTest.php
│   └── Controller/
│       └── SecurityControllerTest.php
└── Functional/
    ├── LoginTest.php
    └── RegistrationTest.php
```

### 2.2 Exemple de test unitaire

```php
// tests/Unit/Entity/PersonTest.php
class PersonTest extends TestCase
{
    public function testSetFirstNameCapitalizesFirstLetter(): void
    {
        $person = new Person();
        $person->setFirstName('john');

        self::assertSame('John', $person->getFirstName());
    }

    public function testCreateMissingCharacteristicsAddsNewOnes(): void
    {
        $person = new Person();
        $type = new CharacteristicType();
        $type->setTitle('Test');

        $person->createMissingCharacteristics([$type]);

        self::assertCount(1, $person->getCharacteristics());
    }

    public function testColorIsValidHexOnConstruction(): void
    {
        $person = new Person();

        self::assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $person->getColor());
    }
}
```

### 2.3 Exemple de test d'intégration

```php
// tests/Integration/Repository/PersonRepositoryTest.php
class PersonRepositoryTest extends KernelTestCase
{
    private PersonRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = self::getContainer()->get(PersonRepository::class);
    }

    public function testGetByIdentityReturnsPerson(): void
    {
        $person = $this->repository->getByIdentity('John', 'Doe');

        self::assertInstanceOf(Person::class, $person);
        self::assertSame('John', $person->getFirstName());
    }

    public function testGetAllOrdersByField(): void
    {
        $persons = $this->repository->getAll('lastName');

        $lastNames = array_map(fn(Person $p) => $p->getLastName(), $persons);
        $sorted = $lastNames;
        sort($sorted);

        self::assertSame($sorted, $lastNames);
    }
}
```

### 2.4 Exemple de test fonctionnel

```php
// tests/Functional/LoginTest.php
class LoginTest extends WebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            '_username' => 'invalid@email.com',
            '_password' => 'wrongpassword',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/login');
    }
}
```

### 2.5 Objectif de couverture

| Composant | Couverture cible |
|-----------|------------------|
| Entités | 90% |
| Repositories | 80% |
| Services | 85% |
| Contrôleurs | 70% |
| Voters | 95% |
| **Global** | **75%** |

---

## Phase 3: Documentation (Priorité P2)

### 3.1 JSDoc pour JavaScript

```javascript
/**
 * Encode les données du formulaire en JSON, incluant l'image en base64.
 * @returns {Promise<string>} Les données du formulaire en JSON
 */
async function formEncodeJson() {
```

### 3.2 README amélioré

Voir le fichier CLAUDE.md créé comme base.

---

## Phase 4: Améliorations SCSS (Priorité P3)

### 4.1 Variables centralisées

Déplacer toutes les couleurs vers `_colors.scss`:
```scss
// assets/styles/base/_colors.scss
:root {
    // Primary
    --color-primary: hsl(216, 48%, 14%);
    --color-primary-light: hsl(216, 48%, 58%);

    // Neutral
    --color-neutral-100: hsl(0, 0%, 100%);
    --color-neutral-200: hsl(0, 0%, 94%);
    --color-neutral-500: hsl(0, 0%, 54%);

    // Semantic
    --color-success: hsl(120, 40%, 50%);
    --color-error: hsl(0, 70%, 50%);
    --color-warning: hsl(45, 100%, 50%);
}
```

### 4.2 Typage des composants

Créer des mixins typés pour les composants:
```scss
// assets/styles/abstracts/_components.scss
@mixin card($padding: 1rem, $radius: 0.5rem) {
    padding: $padding;
    border-radius: $radius;
    background: var(--color-neutral-100);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

@mixin button-variant($bg, $color, $hover-bg) {
    background: $bg;
    color: $color;

    &:hover {
        background: $hover-bg;
    }
}
```

---

## Phase 5: JavaScript Modernisation (Priorité P3)

### 5.1 Migration vers TypeScript (optionnel)

```typescript
// assets/scripts/editPerson.ts
interface FieldReferences {
    form: HTMLFormElement;
    bio: HTMLTextAreaElement;
    picture: HTMLInputElement;
    // ...
}

const field: FieldReferences = {
    form: document.querySelector(".edit-person") as HTMLFormElement,
    bio: document.querySelector("#bio-field") as HTMLTextAreaElement,
    // ...
};
```

### 5.2 Gestion d'erreurs améliorée

```javascript
// assets/scripts/utils/api.js
class ApiError extends Error {
    constructor(message, code, details = []) {
        super(message);
        this.code = code;
        this.details = details;
    }
}

async function fetchApi(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers,
            },
        });

        const data = await response.json();

        if (!response.ok || data.code !== 200) {
            throw new ApiError(
                data.message || 'Erreur serveur',
                data.code || response.status,
                data.errors || []
            );
        }

        return data;
    } catch (error) {
        if (error instanceof ApiError) {
            throw error;
        }
        throw new ApiError('Erreur réseau', 0, [error.message]);
    }
}
```

---

## Métriques de Succès

| Métrique | Actuel | Cible |
|----------|--------|-------|
| Couverture tests | ~0% | 75% |
| Complexité cyclomatique max | 25+ | 10 |
| PHPStan erreurs | 0 (avec ignores) | 0 (sans ignores) |
| Lignes par méthode max | 140+ | 30 |
| Code dupliqué | ~5% | < 2% |

---

## Checklist Qualité

### Avant chaque commit
- [ ] `composer phpstan` passe
- [ ] `composer phpcs` passe
- [ ] `composer test` passe
- [ ] Pas de `@phpstan-ignore` ajoutés

### Avant chaque release
- [ ] `composer rector-dry` vérifié
- [ ] Couverture tests maintenue
- [ ] Documentation à jour
