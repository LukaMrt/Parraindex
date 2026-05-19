import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LUKA_EMAIL, DEFAULT_PASSWORD, loginAs, getMe } from '../../helpers/auth';
import { assertDefined } from '../../helpers/assert';

interface SponsorLink {
  id: number;
  godFatherName: string;
  type: string;
  date: string | null;
}

interface PersonResponse {
  data: { godFathers: SponsorLink[] };
}

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('edit type + date of an existing sponsor link (Lilian → Luka)', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  // Récupérer l'ID du sponsor Lilian → Luka via l'API
  const personResp = await page.request.get(`/api/persons/${me.person.id.toString()}`);
  expect(personResp.ok()).toBeTruthy();
  const body = (await personResp.json()) as PersonResponse;
  const lilianLink = assertDefined(
    body.data.godFathers.find((s) => /lilian/i.test(s.godFatherName)),
    'Lilian sponsor link in fixtures',
  );
  const sponsorId = lilianLink.id;
  expect(lilianLink.type).toBe('CLASSIC');

  await page.goto(`/person/${me.person.id.toString()}/edit`);

  // Cibler la ligne du sponsor via data-testid, cliquer sur "Modifier ce parrainage"
  const row = page.getByTestId(`sponsor-row-${sponsorId.toString()}`);
  await expect(row).toBeVisible();
  await row.getByTitle('Modifier ce parrainage').click();

  // Changer le type → FALUCHE
  await row.getByRole('button', { name: /Faluchard/i }).click();

  // Changer la date
  await row.locator('input[type="date"]').fill('2021-09-15');

  // Enregistrer
  await row.getByRole('button', { name: 'Enregistrer' }).click();

  await expect(page.getByText('Parrainage mis à jour')).toBeVisible({ timeout: 5_000 });

  // Vérification API : le sponsor a maintenant le type FALUCHE
  const personRespAfter = await page.request.get(`/api/persons/${me.person.id.toString()}`);
  const bodyAfter = (await personRespAfter.json()) as PersonResponse;
  const updated = bodyAfter.data.godFathers.find((s) => s.id === sponsorId);
  expect(updated?.type).toBe('FALUCHE');
  expect(updated?.date).toContain('2021-09-15');
});
