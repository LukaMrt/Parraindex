# set shell := ["powershell", "-Command"]

image := "lukamrt/parraindex"

# ── Docker image (prod) ──────────────────────────────────────────────────────

build-push:
    docker buildx build --platform linux/arm64 --push -t {{image}}:latest --target prod -f docker/Dockerfile .

# ── E2E ──────────────────────────────────────────────────────────────────────

# Démarre la stack en mode test (image Docker avec dev deps, APP_ENV=dev)
e2e-up:
    docker compose -f compose.yaml -f compose.e2e.yaml up -d --build

# Arrête la stack e2e
e2e-down:
    docker compose -f compose.yaml -f compose.e2e.yaml down

# Recharge la base avec les fixtures (utile pour debug, sinon les tests le font tout seuls)
e2e-fixtures:
    docker compose -f compose.yaml -f compose.e2e.yaml exec -T app php bin/console doctrine:fixtures:load --no-interaction

# Installe les deps Playwright + le browser chromium
e2e-install:
    cd e2e; npm ci
    cd e2e; npx playwright install --with-deps chromium

# Lance les tests e2e (suppose la stack déjà démarrée via `just e2e-up`)
e2e-test:
    cd e2e; npm test

# Mode UI Playwright (lancement interactif, watch, visualisation des traces)
e2e-ui:
    cd e2e; npm run test:ui

# Recipe combinée : up → install → test (idempotent, à utiliser pour un run complet)
e2e: e2e-up e2e-install e2e-test

# Qualité (mêmes règles que le frontend)
e2e-format:
    cd e2e; npm run format:check

e2e-lint:
    cd e2e; npm run lint

e2e-typecheck:
    cd e2e; npm run typecheck

e2e-check: e2e-format e2e-lint e2e-typecheck
