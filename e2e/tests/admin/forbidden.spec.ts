import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LILIAN_EMAIL, DEFAULT_PASSWORD, loginAs } from '../../helpers/auth';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('regular user (Lilian) cannot access /admin', async ({ page }) => {
  await loginAs(page, LILIAN_EMAIL, DEFAULT_PASSWORD);

  const response = await page.goto('/admin');

  // EasyAdmin renvoie 403 quand le user n'a pas ROLE_ADMIN.
  // Selon la config sécurité, ça peut aussi être une redirection.
  // On vérifie que l'utilisateur n'a PAS accès : soit 403, soit redirigé hors de /admin.
  const status = response?.status() ?? 0;
  if (status === 403) {
    // OK, accès refusé directement
    expect(status).toBe(403);
  } else {
    // Soit redirigé hors de /admin, soit page chargée mais sans accès aux entités
    await expect(page).not.toHaveURL(/\/admin\/?\??/);
  }
});
