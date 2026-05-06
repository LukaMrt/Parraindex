# Parraindex

Annuaire des parrainages de l'IUT Lyon 1.

## Structure
- `backend/` — API Symfony (voir `backend/CLAUDE.md`)
- `frontend/` — React + TypeScript + Vite + Tailwind v4 (voir `frontend/CLAUDE.md`)
- `docker/` + `compose.yaml` — orchestration locale

## Démarrage rapide
```bash
docker compose up -d   # backend + BDD
cd frontend && npm run dev
```
