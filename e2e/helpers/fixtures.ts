import { execSync } from 'node:child_process';
import { resolve } from 'node:path';

const REPO_ROOT = resolve(import.meta.dirname, '..', '..');
const MAILPIT_URL = process.env.MAILPIT_URL ?? 'http://localhost:8025';
const COMPOSE = process.env.E2E_COMPOSE_CMD ?? 'docker compose -f compose.yaml -f compose.e2e.yaml';

export function resetFixtures(): void {
  execSync(`${COMPOSE} exec -T app php bin/console doctrine:fixtures:load --no-interaction`, {
    cwd: REPO_ROOT,
    stdio: 'pipe',
  });
}

export function clearMailpit(): void {
  execSync(`curl -sf -X DELETE ${MAILPIT_URL}/api/v1/messages`, { stdio: 'pipe' });
}
