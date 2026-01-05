# Plan d'Amélioration - Performance et Optimisation

## Objectif
Optimiser les performances de l'application pour une meilleure expérience utilisateur et une utilisation efficace des ressources.

---

## Phase 1: Optimisation Base de Données (Priorité P1)

### 1.1 Correction des requêtes N+1

**Problème identifié**: Chargement lazy des relations dans les templates.

**Fichiers concernés**:
- `src/Repository/PersonRepository.php`
- `templates/person.html.twig`

**Solution**: Créer des méthodes avec JOIN FETCH

```php
// src/Repository/PersonRepository.php

/**
 * Récupère une personne avec toutes ses relations chargées.
 */
public function findWithRelations(int $id): ?Person
{
    return $this->createQueryBuilder('p')
        ->leftJoin('p.godFathers', 'gf')
        ->addSelect('gf')
        ->leftJoin('gf.godFather', 'gfp')
        ->addSelect('gfp')
        ->leftJoin('p.godChildren', 'gc')
        ->addSelect('gc')
        ->leftJoin('gc.godChild', 'gcp')
        ->addSelect('gcp')
        ->leftJoin('p.characteristics', 'c')
        ->addSelect('c')
        ->leftJoin('c.type', 'ct')
        ->addSelect('ct')
        ->where('p.id = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->getOneOrNullResult();
}
```

**Utilisation dans le contrôleur**:
```php
#[Route('/{id}', name: 'person')]
public function index(int $id): Response
{
    $person = $this->personRepository->findWithRelations($id);

    if ($person === null) {
        throw $this->createNotFoundException();
    }

    return $this->render('person.html.twig', ['person' => $person]);
}
```

### 1.2 Index de base de données

**Migration recommandée**:
```php
// migrations/VersionXXXX_AddIndexes.php
public function up(Schema $schema): void
{
    // Index sur les recherches fréquentes
    $this->addSql('CREATE INDEX idx_person_name ON person (first_name, last_name)');
    $this->addSql('CREATE INDEX idx_person_start_year ON person (start_year)');
    $this->addSql('CREATE INDEX idx_sponsor_godfather ON sponsor (god_father_id)');
    $this->addSql('CREATE INDEX idx_sponsor_godchild ON sponsor (god_child_id)');
    $this->addSql('CREATE INDEX idx_contact_type ON contact (type)');
    $this->addSql('CREATE INDEX idx_contact_resolution ON contact (resolution_date)');
}
```

---

## Phase 2: Optimisation des Assets (Priorité P2)

### 2.1 Compression des images

**Installation**:
```bash
composer require intervention/image
```

**Service d'optimisation**:
```php
// src/Service/ImageOptimizer.php
use Intervention\Image\ImageManager;

class ImageOptimizer
{
    private const MAX_WIDTH = 800;
    private const MAX_HEIGHT = 800;
    private const QUALITY = 80;

    public function optimize(string $sourcePath, string $destinationPath): void
    {
        $manager = new ImageManager(['driver' => 'gd']);

        $image = $manager->make($sourcePath);

        // Redimensionner si nécessaire
        if ($image->width() > self::MAX_WIDTH || $image->height() > self::MAX_HEIGHT) {
            $image->resize(self::MAX_WIDTH, self::MAX_HEIGHT, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Sauvegarder en WebP si supporté
        $extension = pathinfo($destinationPath, PATHINFO_EXTENSION);
        if (function_exists('imagewebp') && $extension !== 'gif') {
            $destinationPath = preg_replace('/\.\w+$/', '.webp', $destinationPath);
            $image->save($destinationPath, self::QUALITY, 'webp');
        } else {
            $image->save($destinationPath, self::QUALITY);
        }
    }
}
```

### 2.2 Lazy loading des images

**Template Twig**:
```twig
<img
    src="{{ asset('images/placeholder.svg') }}"
    data-src="{{ asset('images/pictures/' ~ person.picture) }}"
    loading="lazy"
    class="lazy-image"
    alt="{{ person.fullName }}"
>
```

**JavaScript**:
```javascript
// assets/scripts/lazyload.js
document.addEventListener('DOMContentLoaded', () => {
    const lazyImages = document.querySelectorAll('.lazy-image');

    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy-image');
                imageObserver.unobserve(img);
            }
        });
    });

    lazyImages.forEach(img => imageObserver.observe(img));
});
```

### 2.3 Minification et bundling

**Configuration de l'asset mapper** (production):
```yaml
# config/packages/asset_mapper.yaml
when@prod:
    framework:
        asset_mapper:
            # Active la compression
            strict_mode: true
```

**Commande de build**:
```bash
php bin/console asset-map:compile
```

### 2.4 Préchargement des fonts

```twig
{# templates/layouts/base.html.twig #}
<link rel="preload" href="{{ asset('fonts/Lato-Regular.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="{{ asset('fonts/Lato-Bold.woff2') }}" as="font" type="font/woff2" crossorigin>
```

---

## Phase 3: Optimisation PHP (Priorité P2)

### 3.1 OPcache configuration (production)

```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.enable_file_override=1
```

### 3.2 Preloading

```php
// config/preload.php (déjà présent)
<?php

if (file_exists(dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php')) {
    require dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php';
}
```

### 3.3 JIT compilation (PHP 8.4)

```ini
; php.ini
opcache.jit_buffer_size=256M
opcache.jit=function
```

---

## Phase 4: Frontend Performance (Priorité P3)

### 4.1 Critical CSS

Extraire le CSS critique pour le rendu initial:

```twig
{# templates/layouts/base.html.twig #}
<style>
    /* CSS critique inline */
    body { margin: 0; font-family: Lato, sans-serif; }
    .navbar { /* styles critiques */ }
</style>
<link rel="preload" href="{{ asset('styles/app.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
```

### 4.2 Defer JavaScript

```twig
<script type="module" defer>
    {% block importmap %}{{ importmap('home') }}{% endblock %}
</script>
```

### 4.3 Service Worker (PWA optionnel)

```javascript
// public/sw.js
const CACHE_NAME = 'parraindex-v1';
const STATIC_ASSETS = [
    '/',
    '/tree',
    '/assets/styles/base.css',
    '/assets/images/icons/logo.svg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request);
        })
    );
});
```

---

## Phase 5: Monitoring (Priorité P2)

### 5.1 Logging des performances

```php
// src/EventListener/PerformanceListener.php
#[AsEventListener(event: KernelEvents::REQUEST, priority: 1000)]
#[AsEventListener(event: KernelEvents::RESPONSE, priority: -1000)]
class PerformanceListener
{
    private float $startTime;

    public function onKernelRequest(): void
    {
        $this->startTime = microtime(true);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $duration = (microtime(true) - $this->startTime) * 1000;

        $event->getResponse()->headers->set('X-Response-Time', sprintf('%.2fms', $duration));

        if ($duration > 500) {
            // Log les requêtes lentes
            $this->logger->warning('Slow request', [
                'path' => $event->getRequest()->getPathInfo(),
                'duration' => $duration,
            ]);
        }
    }
}
```

### 5.2 Doctrine SQL Logger (développement)

```yaml
# config/packages/doctrine.yaml
when@dev:
    doctrine:
        dbal:
            logging: true
            profiling: true
```

---

## Métriques de Performance Cibles

| Métrique | Actuel | Cible |
|----------|--------|-------|
| Time to First Byte (TTFB) | ~300ms | < 100ms |
| Largest Contentful Paint (LCP) | ~2s | < 1.5s |
| First Input Delay (FID) | ~100ms | < 50ms |
| Cumulative Layout Shift (CLS) | ~0.1 | < 0.05 |
| Requêtes DB par page | ~10+ | < 5 |
| Temps de réponse moyen | ~400ms | < 200ms |

---

## Checklist Optimisation

### Avant déploiement
- [ ] Cache Doctrine configuré
- [ ] OPcache activé
- [ ] Assets compilés
- [ ] Indexes DB créés

### En production
- [ ] HTTP cache configuré
- [ ] CDN configuré (optionnel)
- [ ] Monitoring actif
- [ ] Logs de performance analysés

### Tests de performance
- [ ] Lighthouse score > 90
- [ ] WebPageTest validé
- [ ] Tests de charge réalisés
