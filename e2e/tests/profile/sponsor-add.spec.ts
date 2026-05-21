import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LUKA_EMAIL, DEFAULT_PASSWORD, loginAs, getMe } from '../../helpers/auth';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('add a HEART sponsor link from Luka to Henri', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);
  await page.goto(`/person/${me.person.id.toString()}/edit`);

  // Section "Ajouter un parrainage"
  await expect(page.getByText('Ajouter un parrainage')).toBeVisible();

  // Rôle Parrain (Luka devient parrain de quelqu'un)
  await page.getByRole('button', { name: 'Parrain', exact: true }).click();

  // Autocomplete : taper "Henri" → cliquer sur "Henri Durand"
  const autocomplete = page.getByPlaceholder('Rechercher une personne…');
  await autocomplete.fill('Henri');
  const henriOption = page.getByRole('listitem').filter({ hasText: 'Henri Durand' }).first();
  await expect(henriOption).toBeVisible({ timeout: 5_000 });
  await henriOption.click();

  // Type HEART (bouton "De cœur")
  await page.getByRole('button', { name: /De cœur/i }).click();

  // Date
  await page.locator('input[type="date"]').fill('2023-09-01');

  // Bouton "Ajouter"
  await page.getByRole('button', { name: 'Ajouter', exact: true }).click();

  // Notification de succès
  await expect(page.getByText('Parrainage ajouté')).toBeVisible({ timeout: 5_000 });

  // Une SponsorRow avec Henri Durand apparaît dans la section "Fillots"
  // (le nom est rendu en MAJUSCULES dans la SponsorRow)
  await expect(page.getByText('Henri DURAND').first()).toBeVisible();

  // Vérification API : Luka a maintenant Henri parmi ses godChildren
  const personResp = await page.request.get(`/api/persons/${me.person.id.toString()}`);
  expect(personResp.ok()).toBeTruthy();
  const body = (await personResp.json()) as {
    data: { godChildren: { godChildName: string; type: string }[] };
  };
  const henriLink = body.data.godChildren.find((c) => /henri/i.test(c.godChildName));
  expect(henriLink).toBeDefined();
  expect(henriLink?.type).toBe('HEART');
});
