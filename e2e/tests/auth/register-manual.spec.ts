import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';

const NON_UNI_EMAIL = 'newcomer@gmail.com';
const PASSWORD = 'SuperPassword123!';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('register with non-university email → select-person → manual association', async ({
  page,
}) => {
  // 1. Inscription avec email non-universitaire → redirige /select-person
  await page.goto('/register');
  await page.getByPlaceholder('votre@email.com').fill(NON_UNI_EMAIL);
  await page.getByPlaceholder('Mot de passe').fill(PASSWORD);
  await page.getByRole('button', { name: /S['']inscrire/i }).click();

  await expect(page).toHaveURL(/\/select-person$/);

  // 2. Sélection manuelle de Henri Durand
  await page.getByPlaceholder('Rechercher par nom…').fill('Henri Durand');
  const henriButton = page.getByRole('button', { name: /Henri Durand/i });
  await expect(henriButton).toBeVisible({ timeout: 10_000 });
  await henriButton.click();

  await page.getByRole('button', { name: 'Confirmer' }).click();

  // 3. Redirige vers /login
  await expect(page).toHaveURL(/\/login$/, { timeout: 10_000 });

  // 4. Login impossible : le compte n'est pas validé (isValidated = false)
  await page.getByPlaceholder('Email').fill(NON_UNI_EMAIL);
  await page.getByPlaceholder('Mot de passe').fill(PASSWORD);
  await page.getByRole('button', { name: /se connecter/i }).click();

  // L'API doit refuser l'accès tant que l'admin n'a pas validé.
  // Soit on reste sur /login avec une erreur, soit on est redirigé sur /. On vérifie via /api/auth/me.
  await page.waitForTimeout(1000);
  const me = await page.request.get('/api/auth/me');
  if (me.ok()) {
    const body = (await me.json()) as { data: { isValidated: boolean; email: string } };
    expect(body.data.email).toBe(NON_UNI_EMAIL);
    expect(body.data.isValidated).toBe(false);
  } else {
    expect(me.status()).toBe(401);
  }
});
