import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LUKA_EMAIL, DEFAULT_PASSWORD, loginAs, getMe } from '../../helpers/auth';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('edit page shows existing filieres pre-filled', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}/edit`);

  await expect(page.getByRole('heading', { name: 'Filières' })).toBeVisible();

  // Les champs filière et école doivent être pré-remplis avec les fixtures
  await expect(page.getByPlaceholder('Filière').first()).toHaveValue('Informatique');
  await expect(page.getByPlaceholder('École (optionnel)').first()).toHaveValue('IUT Lyon 1');
});

test('can add a new filiere and save', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}/edit`);

  await page.getByRole('button', { name: '+ Ajouter une filière' }).click();

  const rows = page.getByPlaceholder('Filière');
  const lastRow = rows.last();
  await lastRow.fill('Génie Électrique');

  const schoolRows = page.getByPlaceholder('École (optionnel)');
  await schoolRows.last().fill('IUT Lyon 1');

  const yearInputs = page.locator('input[type="number"]');
  // Les inputs number : paires (début, fin) par ligne — le dernier début
  const startInputs = await yearInputs.all();
  await startInputs[startInputs.length - 2].fill('2025');

  await page.getByRole('button', { name: 'Enregistrer' }).click();

  await expect(page).toHaveURL(new RegExp(`/person/${me.person.id.toString()}$`), {
    timeout: 10_000,
  });

  // Vérifier que la nouvelle filière apparaît dans le profil
  await expect(page.getByText('Génie Électrique')).toBeVisible();
});

test('can remove a filiere and save', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}/edit`);

  // Attendre que la page soit chargée avant de compter
  await expect(page.getByPlaceholder('Filière').first()).toBeVisible();
  const rowsBefore = await page.getByPlaceholder('Filière').count();
  expect(rowsBefore).toBeGreaterThan(0);

  await page.getByTestId('filiere-remove').last().click();

  const rowsAfter = await page.getByPlaceholder('Filière').count();
  expect(rowsAfter).toBe(rowsBefore - 1);

  await page.getByRole('button', { name: 'Enregistrer' }).click();

  await expect(page).toHaveURL(new RegExp(`/person/${me.person.id.toString()}$`), {
    timeout: 10_000,
  });
});

test('filiere autocomplete suggests existing filieres', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}/edit`);

  await page.getByRole('button', { name: '+ Ajouter une filière' }).click();

  const newFiliereInput = page.getByPlaceholder('Filière').last();
  await newFiliereInput.fill('Info');

  // Le datalist doit contenir "Informatique"
  const datalistId = await newFiliereInput.getAttribute('list');
  expect(datalistId).toBeTruthy();
  if (!datalistId) return;
  const option = page.locator(`#${datalistId} option[value="Informatique"]`);
  await expect(option).toBeAttached();
});

test('school autocomplete suggests existing schools', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}/edit`);

  await page.getByRole('button', { name: '+ Ajouter une filière' }).click();

  const newSchoolInput = page.getByPlaceholder('École (optionnel)').last();
  await newSchoolInput.fill('IUT');

  const datalistId = await newSchoolInput.getAttribute('list');
  expect(datalistId).toBeTruthy();
  if (!datalistId) return;
  const option = page.locator(`#${datalistId} option[value="IUT Lyon 1"]`);
  await expect(option).toBeAttached();
});
