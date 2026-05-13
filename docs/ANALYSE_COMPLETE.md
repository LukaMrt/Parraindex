# Analyse Complète du Projet Parraindex

## 1. Vue d'ensemble

**Parraindex** est une application web moderne développée avec Symfony 7.2 et PHP 8.4. Elle gère les relations de
parrainage entre étudiants de l'IUT Lyon 1.

### Statistiques du projet

| Métrique            | Valeur       |
|---------------------|--------------|
| Fichiers PHP source | ~50 fichiers |
| Lignes de code PHP  | ~2,600 LOC   |
| Templates Twig      | ~35 fichiers |
| Fichiers SCSS       | 60+ fichiers |
| Modules JavaScript  | 18 fichiers  |
| Entités Doctrine    | 11 classes   |
| Contrôleurs         | 11 classes   |

---

## 2. Analyse de la Qualité du Code

### 2.1 Points Positifs

#### Architecture

- **MVC bien respecté**: Séparation claire entre contrôleurs, entités et vues
- **Repository Pattern**: Accès aux données centralisé et cohérent
- **Voter Pattern**: Autorisation fine et maintenable
- **Composants Twig réutilisables**: DRY appliqué pour les formulaires

#### Typage et Modernité

- `declare(strict_types=1)` sur tous les fichiers PHP
- Utilisation des attributs PHP 8 (Route, ORM, Assert)
- Enums PHP 8.1 pour les types (`Role`, `Type`, `SponsorType`)
- Fluent interface sur les setters

#### Outils de Qualité

- PHPStan niveau max configuré
- PHP CodeSniffer pour le style
- Rector pour la modernisation
- Infection pour les tests de mutation

### 2.2 Points d'Amélioration

#### Couverture de tests insuffisante

```
tests/
├── bootstrap.php
└── FakeTest.php    # Seulement un test factice !
```

**Impact**: Risque de régression élevé, refactoring difficile.

#### Commentaires PHPStan ignorés

```php
// @phpstan-ignore-next-line
$this->addFlash('error', $error->getMessage());
```

**Impact**: Cache potentiellement de vrais problèmes de typage.

#### Méthode AdminController::resolve() trop longue

- 140+ lignes avec un switch de 10 cas
- Logique métier dans le contrôleur
- Violation du Single Responsibility Principle

#### Duplication de code

- Logique de recherche de Person répétée dans AdminController
- Pattern similaire pour Person1 et Person2

#### Code mort commenté

```php
//        $contact->setResolutionDate(new \DateTime());
//        $this->contactRepository->update($contact);
```

---

## 3. Analyse de la Propreté

### 3.1 Points Positifs

#### Nommage cohérent

- Contrôleurs suffixés par `Controller`
- Repositories suffixés par `Repository`
- Voters suffixés par `Voter`
- Conventions Symfony respectées

#### Organisation des fichiers

- Structure claire par domaine (`Person/`, `Sponsor/`, `Contact/`)
- Séparation frontend (assets/) et backend (src/)
- Configuration centralisée (config/)

#### SCSS bien organisé

```
styles/
├── abstracts/    # Utilitaires
├── base/         # Reset, typo
├── components/   # Composants
└── layout/       # Mise en page
```

### 3.2 Points d'Amélioration

#### Fichiers JavaScript sans typage

- Pas de TypeScript
- Pas de JSDoc
- Risque d'erreurs runtime

#### Variables magiques

```javascript
const personId = Number(document.querySelector(".card").id);
```

Devrait être passé via data-attribute explicite.

#### Gestion d'erreurs JavaScript basique

```javascript
if (!request.ok) {
    triggerErrorPopup(["Une erreur est survenue..."]);
}
```

Pas de distinction des types d'erreurs (réseau, serveur, validation).

#### Entité Contact trop complexe

- 14 propriétés dont beaucoup conditionnelles
- Validation complexe avec expressions
- Devrait peut-être être décomposée en types spécifiques

---

## 4. Analyse de la Sécurité

### 4.1 Points Positifs

#### Authentification robuste

- Hashage automatique des mots de passe (bcrypt/Argon2)
- Vérification de mots de passe compromis (`NotCompromisedPassword`)
- Validation de force de mot de passe
- Tokens CSRF sur tous les formulaires

#### Autorisation fine

- Voters personnalisés pour chaque ressource
- Restriction d'email universitaire
- Routes admin protégées
- Vérification d'email obligatoire

#### Protection des données

- Export RGPD disponible (`DataController`)
- Seul le propriétaire peut éditer son profil

### 4.2 Vulnérabilités Potentielles

#### Injection potentielle dans orderBy

```php
public function getAll(string $orderBy = 'id'): array
{
    $result = $this->createQueryBuilder('p')
        ->orderBy('p.' . $orderBy, 'ASC')  // Non validé !
```

**Risque**: SQL injection si `$orderBy` provient de l'utilisateur.
**Correction**: Whitelist des colonnes autorisées.

#### Parsing d'email fragile

```php
$emailParts = explode('@', $email);
$emailParts = explode('.', $emailParts[0]);
$firstName = ucfirst($emailParts[0]);
$lastName  = ucfirst($emailParts[1]);  // Peut échouer !
```

**Risque**: Exception si email malformé malgré le regex.
**Correction**: Validation supplémentaire après parsing.

#### Parsing de noms fragile dans ContactType

```php
$names = explode(' ', $data['relatedPerson']);
$data['relatedPersonFirstName'] = $names[0];
$data['relatedPersonLastName']  = $names[1];  // IndexOutOfBounds !
```

**Risque**: Exception si nom incomplet.
**Correction**: Vérifier `count($names) >= 2`.

#### Mot de passe en clair dans Contact

```php
#[ORM\Column(length: 255, nullable: true)]
private ?string $password = null;
```

**Risque**: Le mot de passe est stocké en clair dans la table Contact !
**Correction**: Ne jamais stocker de mot de passe, même temporairement.

#### Taille d'upload non vérifiée côté serveur

```javascript
if (picture.size > 5_000 * 1024) {
    alert("Attention, la taille de l'image ne doit pas dépasser 5Mo");
```

La vérification est uniquement côté client (JavaScript).
**Risque**: Bypass possible, déni de service.
**Correction**: Ajouter validation côté serveur.

#### DELETE via GET

```php
#[Route('/admin/contact/{id}/delete', name: 'admin_contact_delete', methods: [Request::METHOD_GET])]
public function delete(Contact $contact): Response
```

**Risque**: CSRF potentiel car DELETE devrait être POST/DELETE.

---

## 5. Analyse de l'Optimisation

### 5.1 Points Positifs

#### Configuration Doctrine optimisée

- Lazy loading activé
- Lazy ghost objects activés
- Cache de requêtes en production
- Proxy pre-generation en production

#### Assets légers

- Pas de framework JavaScript lourd
- Modules chargés par page (importmap)
- SCSS compilé

#### Docker optimisé

- Multi-stage build
- FrankenPHP (performances natives)
- Alpine Linux (image légère)

### 5.2 Points d'Amélioration

#### Requêtes N+1 potentielles

```php
public function index(Person $person): Response
{
    return $this->render('person.html.twig', ['person' => $person]);
}
```

Si le template accède à `person.godFathers`, `person.characteristics`, etc., chaque relation génère une requête.

**Solution**: Eager loading ou JOIN FETCH dans le repository.

#### Pas de cache applicatif

Aucun cache de résultats fréquemment accédés (liste de personnes, statistiques).

**Solution**: Utiliser Symfony Cache pour les données lourdes.

#### Images non optimisées

Les images uploadées ne sont pas:

- Redimensionnées
- Compressées
- Converties en WebP

**Solution**: Utiliser Imagine ou intervention/image.

#### CSS non splitté

Un seul fichier SCSS par page, mais incluant tous les composants.

**Solution**: Utiliser `@import` conditionnel ou CSS-in-JS.

#### Pas de pagination

```php
public function getAll(string $orderBy = 'id'): array
```

Retourne toutes les entités sans limite.

**Solution**: Implémenter pagination avec Pagerfanta ou KnpPaginator.

---

## 6. Analyse de la Maintenabilité

### 6.1 Points Positifs

- Dépendances à jour (Symfony 7.2, PHP 8.4)
- CI/CD configuré (GitHub Actions)
- Scripts Composer documentés
- Structure standard Symfony

### 6.2 Points d'Amélioration

#### Documentation absente

- Pas de README détaillé
- Pas de documentation API
- Pas de guide de contribution

#### Tests inexistants

- Impossible de refactorer en confiance
- Pas de tests d'intégration
- Pas de tests E2E

#### Logs limités

Seul le warning email est loggé:

```php
$this->logger->warning('Email could not be sent...');
```

Pas de logs métier, pas de traçabilité.

---

## 7. Résumé des Scores

| Aspect              | Score | Commentaire                                |
|---------------------|-------|--------------------------------------------|
| **Architecture**    | 8/10  | Bien structuré, patterns modernes          |
| **Qualité du code** | 7/10  | Bon typage, mais méthodes trop longues     |
| **Propreté**        | 7/10  | Conventions respectées, quelques dettes    |
| **Sécurité**        | 6/10  | Bonnes bases, mais failles à corriger      |
| **Performance**     | 6/10  | Configuration OK, optimisations manquantes |
| **Tests**           | 2/10  | Quasi inexistants                          |
| **Documentation**   | 3/10  | Très insuffisante                          |
| **Maintenabilité**  | 6/10  | Dépend des tests et docs                   |

**Score Global**: **5.6/10** - Projet fonctionnel avec de bonnes bases mais nécessitant des améliorations
significatives.

---

## 8. Recommandations Prioritaires

### Critique (P0)

1. **Corriger la faille de mot de passe en clair** dans Contact
2. **Valider le paramètre orderBy** dans PersonRepository
3. **Ajouter validation côté serveur** pour uploads

### Haute (P1)

4. **Écrire des tests** (minimum 60% de couverture)
5. **Refactorer AdminController::resolve()** en services dédiés
6. **Ajouter pagination** aux listes

### Moyenne (P2)

7. Documenter l'API et le code
8. Optimiser les requêtes Doctrine
9. Ajouter du logging métier

### Basse (P3)

10. Migrer vers TypeScript
11. Optimiser les images
12. Ajouter cache applicatif
