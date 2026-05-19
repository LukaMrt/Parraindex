import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LUKA_EMAIL } from '../../helpers/auth';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('register with an email already linked to a user shows an error', async ({ page }) => {
  await page.goto('/register');
  await page.getByPlaceholder('votre@email.com').fill(LUKA_EMAIL);
  await page.getByPlaceholder('Mot de passe').fill('AnotherPassword123!');
  await page.getByRole('button', { name: /S['']inscrire/i }).click();

  await expect(page).toHaveURL(/\/register$/);
  await expect(page.getByText(/compte existe déjà/i)).toBeVisible();
});
