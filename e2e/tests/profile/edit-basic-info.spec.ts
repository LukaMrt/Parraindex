import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LUKA_EMAIL, DEFAULT_PASSWORD, loginAs, getMe } from '../../helpers/auth';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('admin edits firstName, lastName, startYear and biography of own profile', async ({
  page,
}) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}/edit`);

  await expect(page.getByPlaceholder('Prénom', { exact: true })).toBeVisible();

  await page.getByPlaceholder('Prénom', { exact: true }).fill('Lucas');
  await page.getByPlaceholder('Nom', { exact: true }).fill('Marais');
  await page.getByRole('spinbutton').fill('2020');
  const newBio = `Bio mise à jour par les tests e2e à ${Date.now().toString()}.`;
  await page.getByPlaceholder(/Biographie affichée/i).fill(newBio);

  await page.getByRole('button', { name: 'Enregistrer' }).click();

  await expect(page).toHaveURL(new RegExp(`/person/${me.person.id.toString()}$`), {
    timeout: 10_000,
  });

  await expect(page.getByRole('heading', { level: 1 })).toContainText(/lucas\s+marais/i);
  await expect(page.getByText(newBio)).toBeVisible();
  await expect(page.getByText('Promo 2020', { exact: true })).toBeVisible();
});
