import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LUKA_EMAIL, DEFAULT_PASSWORD, loginAs, getMe } from '../../helpers/auth';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('edit page shows existing associations pre-filled', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}/edit`);

  await expect(page.getByRole('heading', { name: 'Associations' })).toBeVisible();

  // Luka a BDE (Président) dans les fixtures
  await expect(page.getByPlaceholder('Association').first()).toHaveValue('BDE');
  await expect(page.getByPlaceholder('Poste (ex : Président)').first()).toHaveValue('Président');
});

test('can add a new association and save', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}/edit`);

  await page.getByRole('button', { name: '+ Ajouter une association' }).click();

  const nameInputs = page.getByPlaceholder('Association');
  await nameInputs.last().fill('Club Photo');

  const posteInputs = page.getByPlaceholder('Poste (ex : Président)');
  await posteInputs.last().fill('Trésorier');

  await page.getByRole('button', { name: 'Enregistrer' }).click();

  await expect(page).toHaveURL(new RegExp(`/person/${me.person.id.toString()}$`), {
    timeout: 10_000,
  });

  await expect(page.getByText('Club Photo')).toBeVisible();
  await expect(page.getByText('Trésorier')).toBeVisible();
});

test('can remove an association and save', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}/edit`);

  await expect(page.getByPlaceholder('Association').first()).toBeVisible();
  const rowsBefore = await page.getByPlaceholder('Association').count();
  expect(rowsBefore).toBeGreaterThan(0);

  await page.getByTestId('association-remove').last().click();

  const rowsAfter = await page.getByPlaceholder('Association').count();
  expect(rowsAfter).toBe(rowsBefore - 1);

  await page.getByRole('button', { name: 'Enregistrer' }).click();

  await expect(page).toHaveURL(new RegExp(`/person/${me.person.id.toString()}$`), {
    timeout: 10_000,
  });
});

test('association autocomplete suggests existing associations', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}/edit`);

  await page.getByRole('button', { name: '+ Ajouter une association' }).click();

  const newAssocInput = page.getByPlaceholder('Association').last();
  await newAssocInput.fill('BD');

  // Le datalist doit contenir "BDE"
  const datalistId = await newAssocInput.getAttribute('list');
  expect(datalistId).toBeTruthy();
  if (!datalistId) return;
  const option = page.locator(`#${datalistId} option[value="BDE"]`);
  await expect(option).toBeAttached();
});

test('cannot save association with empty name', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}/edit`);

  await page.getByRole('button', { name: '+ Ajouter une association' }).click();

  // Laisser le nom vide et remplir le poste
  const posteInputs = page.getByPlaceholder('Poste (ex : Président)');
  await posteInputs.last().fill('Membre');

  await page.getByRole('button', { name: 'Enregistrer' }).click();

  // Ne doit pas naviguer — reste sur la page d'édition
  await expect(page).toHaveURL(new RegExp(`/person/${me.person.id.toString()}/edit`));
});
