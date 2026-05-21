import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LUKA_EMAIL } from '../../helpers/auth';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('login with wrong password stays on /login and shows error', async ({ page }) => {
  await page.goto('/login');
  await page.getByPlaceholder('Email').fill(LUKA_EMAIL);
  await page.getByPlaceholder('Mot de passe').fill('wrong-password');
  await page.getByRole('button', { name: /se connecter/i }).click();

  await expect(page).toHaveURL(/\/login$/);
  await expect(
    page.getByText(/identifiants|invalid|incorrect|connexion impossible/i),
  ).toBeVisible();

  const me = await page.request.get('/api/auth/me');
  expect(me.status()).toBe(401);
});
