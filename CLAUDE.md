# Parraindex

Annuaire des parrainages de l'IUT Lyon 1.

Architecture SPA découplée : API Symfony + SPA React, servies en prod par un conteneur unique FrankenPHP/Caddy.

## Structure
- `backend/` — API JSON Symfony 8 + back-office EasyAdmin (voir `backend/CLAUDE.md`)
- `frontend/` — React 19 + TypeScript + Vite + Tailwind v4 (voir `frontend/CLAUDE.md`)
- `e2e/` — tests end-to-end Playwright
- `docker/` (`Dockerfile` + `Caddyfile`) + `compose.yaml` — image et stack **prod**
- `justfile` — tâches e2e et build Docker

## Démarrage rapide (dev)
Le frontend Vite (`:3000`) proxifie `/api` vers le backend (cf. `frontend/vite.config.ts`).
```bash
cd backend && composer install && symfony serve   # API sur :8000
cd frontend && npm install && npm run dev          # SPA sur :3000
```
Voir `README.md` pour la config `.env` (variables séparées, pas de DSN) et `just e2e` pour les tests e2e.
