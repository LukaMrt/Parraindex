import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LUKA_EMAIL, DEFAULT_PASSWORD, loginAs, getMe } from '../../helpers/auth';
import { assertDefined } from '../../helpers/assert';

interface SponsorLink {
  id: number;
  godChildName: string;
}

interface PersonResponse {
  data: { godChildren: SponsorLink[] };
}

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('delete an existing sponsor link (Luka → Emma)', async ({ page }) => {
  await loginAs(page, LUKA_EMAIL, DEFAULT_PASSWORD);
  const me = await getMe(page);

  // Récupérer l'ID du sponsor Luka → Emma
  const personResp = await page.request.get(`/api/persons/${me.person.id.toString()}`);
  expect(personResp.ok()).toBeTruthy();
  const body = (await personResp.json()) as PersonResponse;
  const emmaLink = assertDefined(
    body.data.godChildren.find((s) => /emma/i.test(s.godChildName)),
    'Emma sponsor link in fixtures',
  );
  const sponsorId = emmaLink.id;

  await page.goto(`/person/${me.person.id.toString()}/edit`);

  const row = page.getByTestId(`sponsor-row-${sponsorId.toString()}`);
  await expect(row).toBeVisible();

  // Le bouton delete a la confirmation à 2 clics (composant Button avec confirm=true).
  // 1er clic : affiche "Confirmer". 2e clic : exécute la suppression.
  const deleteBtn = row.getByTitle('Supprimer ce parrainage');
  await deleteBtn.click();
  await expect(deleteBtn).toHaveText('Confirmer');
  await deleteBtn.click();

  await expect(page.getByText('Parrainage supprimé')).toBeVisible({ timeout: 5_000 });
  await expect(row).not.toBeVisible();

  // Vérification API : Luka n'a plus Emma parmi ses godChildren
  const personRespAfter = await page.request.get(`/api/persons/${me.person.id.toString()}`);
  const bodyAfter = (await personRespAfter.json()) as PersonResponse;
  const stillThere = bodyAfter.data.godChildren.find((s) => s.id === sponsorId);
  expect(stillThere).toBeUndefined();
});
