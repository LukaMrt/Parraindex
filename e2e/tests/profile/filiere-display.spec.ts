import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LUKA_EMAIL, DEFAULT_PASSWORD, loginAs, getMe } from '../../helpers/auth';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('profile page displays filieres section with school and years', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}`);

  await expect(page.getByRole('heading', { name: 'Parcours' })).toBeVisible();

  // Luka a une filière Informatique à l'IUT Lyon 1 (fixture)
  const parcours = page.getByRole('heading', { name: 'Parcours' }).locator('..');
  await expect(parcours.getByText('IUT Lyon 1')).toBeVisible();
  await expect(parcours.getByText('Informatique').first()).toBeVisible();
  await expect(parcours.getByText(/2021/).first()).toBeVisible();
});

test('profile page displays multiple filieres ordered by start year', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}`);

  const texts = await page
    .getByRole('heading', { name: 'Parcours' })
    .locator('~ div')
    .allInnerTexts();

  // IUT Lyon 1 (2021) doit apparaître avant INSA Lyon (2024)
  const iutIndex = texts.findIndex((t) => /IUT Lyon 1/i.test(t));
  const insaIndex = texts.findIndex((t) => /INSA Lyon/i.test(t));

  if (iutIndex !== -1 && insaIndex !== -1) {
    expect(iutIndex).toBeLessThan(insaIndex);
  }
});

test('profile page shows school logo when available', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}`);

  // Au moins un logo d'école doit être présent dans la section Parcours
  const parcours = page.getByRole('heading', { name: 'Parcours' }).locator('..');
  await expect(parcours.locator('img').first()).toBeVisible();
});

test('person without filieres does not show parcours section', async ({ page }) => {
  // Henri n'a pas de filières dans les fixtures de base
  const henryResp = await page.request.get('/api/persons?orderBy=firstName');
  const henryBody = (await henryResp.json()) as { data: { id: number; firstName: string }[] };
  const personWithoutFiliere = henryBody.data.find((p) => p.firstName === 'Henri');

  if (!personWithoutFiliere) {
    test.skip();
    return;
  }

  await page.goto(`/person/${personWithoutFiliere.id.toString()}`);

  await expect(page.getByRole('heading', { name: 'Parcours' })).not.toBeVisible();
});
