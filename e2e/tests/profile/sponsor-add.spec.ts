import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LUKA_EMAIL, DEFAULT_PASSWORD, loginAs, getMe } from '../../helpers/auth';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test.describe('person autocomplete (async SuggestInput)', () => {
  test('no dropdown with fewer than 2 characters', async ({ page }) => {
    await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
    const me = await getMe(page);
    await page.goto(`/person/${me.person.id.toString()}/edit`);

    const autocomplete = page.getByPlaceholder('Rechercher une personne…');
    await autocomplete.fill('H');

    // minChars = 2 : aucune liste ne doit apparaître
    await expect(page.getByRole('listbox')).not.toBeVisible();
  });

  test('shows loading state then results after debounce', async ({ page }) => {
    await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
    const me = await getMe(page);
    await page.goto(`/person/${me.person.id.toString()}/edit`);

    const autocomplete = page.getByPlaceholder('Rechercher une personne…');
    await autocomplete.fill('Hen');

    // État intermédiaire "Recherche…" pendant le debounce
    await expect(page.getByText('Recherche…')).toBeVisible({ timeout: 1_000 });

    // Résultats après la requête réseau
    const henriOption = page.getByRole('option').filter({ hasText: 'Henri Durand' }).first();
    await expect(henriOption).toBeVisible({ timeout: 5_000 });
  });

  test('current person is excluded from results', async ({ page }) => {
    await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
    const me = await getMe(page);
    await page.goto(`/person/${me.person.id.toString()}/edit`);

    // Récupère le nom complet de l'utilisateur connecté
    const personResp = await page.request.get(`/api/persons/${me.person.id.toString()}`);
    const personBody = (await personResp.json()) as { data: { fullName: string } };
    const myFullName = personBody.data.fullName;

    // Cherche son propre prénom dans l'autocomplete
    const firstName = myFullName.split(' ')[0] ?? 'Luka';
    const autocomplete = page.getByPlaceholder('Rechercher une personne…');
    await autocomplete.fill(firstName);

    await page.waitForTimeout(400); // debounce + réseau

    // Son propre nom ne doit pas apparaître dans la liste
    await expect(page.getByRole('option').filter({ hasText: myFullName })).not.toBeVisible();
  });

  test('selecting a result resets if the user types again', async ({ page }) => {
    await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
    const me = await getMe(page);
    await page.goto(`/person/${me.person.id.toString()}/edit`);

    const autocomplete = page.getByPlaceholder('Rechercher une personne…');
    await autocomplete.fill('Hen');

    const henriOption = page.getByRole('option').filter({ hasText: 'Henri Durand' }).first();
    await expect(henriOption).toBeVisible({ timeout: 5_000 });
    await henriOption.click();

    // L'input affiche le nom complet sélectionné
    await expect(autocomplete).toHaveValue('Henri Durand');

    // Si on retape, la sélection est effacée (bouton Ajouter reste disabled)
    await autocomplete.fill('Hen');
    const addButton = page.getByRole('button', { name: 'Ajouter', exact: true });
    await expect(addButton).toBeDisabled();
  });
});

test('add a HEART sponsor link from Luka to Henri', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);
  await page.goto(`/person/${me.person.id.toString()}/edit`);

  // Section "Ajouter un parrainage"
  await expect(page.getByText('Ajouter un parrainage')).toBeVisible();

  // Rôle Parrain (Luka devient parrain de quelqu'un)
  await page.getByRole('button', { name: 'Parrain', exact: true }).click();

  // Autocomplete : taper "Henri" → attendre les résultats → cliquer sur "Henri Durand"
  const autocomplete = page.getByPlaceholder('Rechercher une personne…');
  await autocomplete.fill('Henri');
  const henriOption = page.getByRole('option').filter({ hasText: 'Henri Durand' }).first();
  await expect(henriOption).toBeVisible({ timeout: 5_000 });
  await henriOption.click();

  // Type HEART (bouton "De cœur")
  await page.getByRole('button', { name: /De cœur/i }).click();

  // Date
  await page.getByTestId('sponsor-date').fill('2023-09-01');

  // Bouton "Ajouter"
  await page.getByRole('button', { name: 'Ajouter', exact: true }).click();

  // Notification de succès
  await expect(page.getByText('Parrainage ajouté')).toBeVisible({ timeout: 5_000 });

  // Une SponsorRow avec Henri Durand apparaît dans la section "Fillots"
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
