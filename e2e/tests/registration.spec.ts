import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../helpers/fixtures';
import { waitForEmailTo, extractVerificationLink } from '../helpers/mailpit';

const EMAIL = 'henri.durand@etu.univ-lyon1.fr';
const PASSWORD = 'SuperPassword123!';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('register → verify email → login → user validated', async ({ page }) => {
  // 1. Inscription
  await page.goto('/register');
  await page.getByPlaceholder('votre@email.com').fill(EMAIL);
  await page.getByPlaceholder('Mot de passe').fill(PASSWORD);
  await page.getByRole('button', { name: /S['']inscrire/i }).click();

  await expect(page).toHaveURL(/\/login$/);

  // 2. Récupération du mail via Mailpit + extraction du lien
  const message = await waitForEmailTo(EMAIL);
  const verifyLink = await extractVerificationLink(message.ID);
  expect(verifyLink).toContain('/verify-email');

  // 3. Clic sur le lien de vérification (redirige vers /login après succès)
  await page.goto(verifyLink);
  await expect(page).toHaveURL(/\/login$/, { timeout: 15_000 });

  // 4. Login
  await page.getByPlaceholder('Email').fill(EMAIL);
  await page.getByPlaceholder('Mot de passe').fill(PASSWORD);
  await page.getByRole('button', { name: /se connecter/i }).click();

  await expect(page).toHaveURL(/\/$/, { timeout: 10_000 });

  // 5. Vérification via /api/auth/me — le compte doit être validé
  const me = await page.request.get('/api/auth/me');
  expect(me.ok()).toBeTruthy();
  const body = (await me.json()) as { data: { email: string; isValidated: boolean } };
  expect(body.data.email).toBe(EMAIL);
  expect(body.data.isValidated).toBe(true);
});
