import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LUKA_EMAIL, DEFAULT_PASSWORD, loginAs, getMe } from '../../helpers/auth';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('profile page displays associations section with name and poste', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}`);

  await expect(page.getByRole('heading', { name: 'Associations' })).toBeVisible();

  // Luka est Président du BDE dans les fixtures
  const assocSection = page.getByRole('heading', { name: 'Associations' }).locator('..');
  await expect(assocSection.getByText('BDE')).toBeVisible();
  await expect(assocSection.getByText('Président')).toBeVisible();
});

test('profile page displays multiple associations', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  await page.goto(`/person/${me.person.id.toString()}`);

  const assocSection = page.getByRole('heading', { name: 'Associations' }).locator('..');
  // Luka a BDE (Président) et Junior Entreprise (Membre) dans les fixtures
  await expect(assocSection.getByText('BDE')).toBeVisible();
  await expect(assocSection.getByText('Junior Entreprise')).toBeVisible();
});

test('person without associations does not show associations section', async ({ page }) => {
  const henryResp = await page.request.get('/api/persons?orderBy=firstName');
  const henryBody = (await henryResp.json()) as { data: { id: number; firstName: string }[] };
  const personWithoutAssociation = henryBody.data.find((p) => p.firstName === 'Henri');

  if (!personWithoutAssociation) {
    test.skip();
    return;
  }

  await page.goto(`/person/${personWithoutAssociation.id.toString()}`);

  await expect(page.getByRole('heading', { name: 'Associations' })).not.toBeVisible();
});
