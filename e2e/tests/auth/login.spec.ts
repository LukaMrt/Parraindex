import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LUKA_EMAIL, DEFAULT_PASSWORD, loginAs, getMe } from '../../helpers/auth';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('login as admin (Luka) then logout', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);

  const me = await getMe(page);
  expect(me.email).toBe(LUKA_EMAIL);
  expect(me.isAdmin).toBe(true);
  expect(me.isValidated).toBe(true);

  await expect(page.getByRole('link', { name: 'Mon compte' })).toBeVisible();
  await expect(page.getByRole('link', { name: 'Administration' })).toBeVisible();

  await page.getByRole('button', { name: 'Déconnexion' }).click();

  await expect(page.getByRole('link', { name: 'Se connecter' })).toBeVisible();
  const meAfter = await page.request.get('/api/auth/me');
  expect(meAfter.status()).toBe(401);
});
