# Roadmap d'Améliorations - Parraindex

## Vue d'ensemble

Ce document présente la feuille de route consolidée des améliorations à apporter au projet Parraindex, organisées par priorité et domaine.

---

## Priorité P0 - Critique (À faire immédiatement)

### Sécurité

| # | Tâche | Fichier | Effort |
|---|-------|---------|--------|
| S1 | Supprimer stockage mot de passe en clair | `Contact.php`, `AdminController.php` | 2h |
| S2 | Valider paramètre orderBy (injection SQL) | `PersonRepository.php` | 30min |
| S3 | Validation upload images côté serveur | Nouveau service | 1h |

**Total estimé**: 3h30

---

## Priorité P1 - Haute (Sprint 1)

### Sécurité

| # | Tâche | Fichier | Effort |
|---|-------|---------|--------|
| S4 | Corriger parsing email | `SecurityController.php` | 1h |
| S5 | Corriger parsing noms | `ContactType.php` | 1h |
| S6 | Utiliser POST/DELETE au lieu de GET | `AdminController.php` | 1h |

### Qualité

| # | Tâche | Fichier | Effort |
|---|-------|---------|--------|
| Q1 | Refactorer AdminController::resolve() | Nouveau pattern Resolver | 4h |
| Q2 | Écrire tests unitaires entités | `tests/Unit/Entity/` | 4h |
| Q3 | Écrire tests intégration repos | `tests/Integration/` | 4h |
| Q4 | Écrire tests fonctionnels auth | `tests/Functional/` | 3h |

### Performance

| # | Tâche | Fichier | Effort |
|---|-------|---------|--------|
| P1 | Corriger requêtes N+1 | `PersonRepository.php` | 2h |
| P2 | Ajouter pagination | Repos + Templates | 3h |
| P3 | Créer indexes DB | Migration | 1h |

**Total estimé**: 24h (3 jours)

---

## Priorité P2 - Moyenne (Sprint 2)

### Sécurité

| # | Tâche | Effort |
|---|-------|--------|
| S7 | Ajouter headers de sécurité | 2h |
| S8 | Implémenter rate limiting | 2h |
| S9 | Ajouter audit logging | 3h |

### Qualité

| # | Tâche | Effort |
|---|-------|--------|
| Q5 | Ajouter PHPDoc complet | 4h |
| Q6 | Supprimer @phpstan-ignore | 1h |
| Q7 | Améliorer gestion erreurs JS | 3h |

### Performance

| # | Tâche | Effort |
|---|-------|--------|
| P4 | Configurer cache applicatif | 3h |
| P5 | Optimiser images (compression) | 3h |
| P6 | Ajouter lazy loading images | 1h |

**Total estimé**: 22h (3 jours)

---

## Priorité P3 - Basse (Sprint 3+)

### Qualité

| # | Tâche | Effort |
|---|-------|--------|
| Q8 | Migrer JS vers TypeScript | 8h |
| Q9 | Améliorer structure SCSS | 4h |
| Q10 | Ajouter JSDoc | 2h |

### Performance

| # | Tâche | Effort |
|---|-------|--------|
| P7 | Critical CSS | 2h |
| P8 | Service Worker (PWA) | 4h |
| P9 | Configurer CDN | 2h |
| P10 | Setup monitoring (Blackfire) | 2h |

**Total estimé**: 24h (3 jours)

---

## Planning Suggéré

```
Semaine 1: P0 (Sécurité critique)
├── Jour 1: S1, S2, S3
└── Jour 2: Tests manuels + déploiement patch

Semaine 2-3: P1 (Fondations)
├── Jours 1-2: Sécurité (S4, S5, S6)
├── Jours 3-4: Refactoring (Q1)
├── Jours 5-7: Tests (Q2, Q3, Q4)
└── Jours 8-9: Performance DB (P1, P2, P3)

Semaine 4-5: P2 (Renforcement)
├── Sécurité (S7, S8, S9)
├── Qualité (Q5, Q6, Q7)
└── Performance (P4, P5, P6)

Semaine 6+: P3 (Polish)
└── Selon les ressources disponibles
```

---

## Métriques de Succès

### Objectifs à atteindre

| Métrique | Avant | Après |
|----------|-------|-------|
| Couverture tests | 0% | 75% |
| Score sécurité | 6/10 | 9/10 |
| Temps réponse moyen | 400ms | 200ms |
| Complexité max méthode | 25+ | 10 |
| Lighthouse Performance | ~70 | 90+ |

### KPIs à suivre

- Nombre de vulnérabilités connues
- Couverture de code
- Temps de réponse P95
- Taux d'erreur 5xx
- Score Lighthouse mensuel

---

## Dépendances Techniques

### Nouvelles dépendances suggérées

```bash
# Tests
composer require --dev phpunit/phpunit

# Performance
composer require babdev/pagerfanta-bundle
composer require intervention/image

# Sécurité
composer require symfony/rate-limiter

# Monitoring (optionnel)
composer require blackfire/php-sdk --dev
```

### Infrastructure

- Redis (pour cache) - optionnel mais recommandé
- CDN (pour assets) - optionnel

---

## Risques et Mitigations

| Risque | Impact | Mitigation |
|--------|--------|------------|
| Régression lors refactoring | Élevé | Écrire tests AVANT refactoring |
| Performance dégradée | Moyen | Benchmarks avant/après |
| Incompatibilité migration | Moyen | Tester en staging |
| Downtime production | Élevé | Blue-green deployment |

---

## Documentation Associée

- `CLAUDE.md` - Guide pour assistants IA
- `docs/ANALYSE_COMPLETE.md` - Analyse détaillée
- `docs/PLAN_AMELIORATION_SECURITE.md` - Plan sécurité
- `docs/PLAN_AMELIORATION_QUALITE.md` - Plan qualité
- `docs/PLAN_AMELIORATION_PERFORMANCE.md` - Plan performance

---

## Prochaines Étapes

1. **Valider** cette roadmap avec l'équipe
2. **Prioriser** selon les besoins business
3. **Créer les tickets** dans le gestionnaire de projet
4. **Commencer** par les corrections P0
5. **Mesurer** les progrès régulièrement
