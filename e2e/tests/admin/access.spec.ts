import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LUKA_EMAIL, DEFAULT_PASSWORD, loginAs } from '../../helpers/auth';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('admin user (Luka) can access /admin dashboard', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);

  const response = await page.goto('/admin');
  expect(response?.status()).toBe(200);

  // L'URL finale doit rester sous /admin (pas redirigée vers /login)
  await expect(page).toHaveURL(/\/admin/);

  // EasyAdmin affiche typiquement une sidebar avec des liens vers les entités
  // gérées. On vérifie qu'au moins l'une des entités principales du projet est visible.
  await expect(
    page.getByRole('link', { name: /personnes?|persons|users?|sponsors?|admin/i }).first(),
  ).toBeVisible();
});
