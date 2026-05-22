import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { assertDefined } from '../../helpers/assert';
import { LUKA_EMAIL, DEFAULT_PASSWORD, loginAs, getMe } from '../../helpers/auth';

interface SponsorLink {
  godFatherId: number;
  godChildId: number;
  godFatherName: string;
  godChildName: string;
}

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('family graph navigation: Luka → Lilian (parrain)', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  // Récupérer le parrain de Luka via /api/persons/{id}
  const personResp = await page.request.get(`/api/persons/${me.person.id.toString()}`);
  expect(personResp.ok()).toBeTruthy();
  const personBody = (await personResp.json()) as { data: { godFathers: SponsorLink[] } };
  const lilianLink = assertDefined(
    personBody.data.godFathers.find((s) => /lilian/i.test(s.godFatherName)),
    'Lilian sponsor link in fixtures',
  );
  const lilianId = lilianLink.godFatherId;

  // Aller sur la fiche Luka
  await page.goto(`/person/${me.person.id.toString()}`);
  await expect(page.getByRole('heading', { level: 1 })).toContainText(/luka\s+maret/i);

  // Cliquer sur le nœud Lilian dans le family graph
  const lilianNode = page.getByTestId(`family-node-${lilianId.toString()}`);
  await expect(lilianNode).toBeVisible();
  await lilianNode.click();

  // On atterrit sur la fiche de Lilian
  await expect(page).toHaveURL(new RegExp(`/person/${lilianId.toString()}$`));
  await expect(page.getByRole('heading', { level: 1 })).toContainText(/lilian/i);
});
