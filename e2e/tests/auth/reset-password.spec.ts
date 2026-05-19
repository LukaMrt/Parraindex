import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';
import { LUKA_EMAIL, loginAs } from '../../helpers/auth';
import { extractLinkMatching, waitForEmailTo } from '../../helpers/mailpit';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('forgot password → email link → temporary password → login', async ({ page }) => {
  // 1. Demande de reset
  await page.goto('/reset-password');
  await page.getByPlaceholder('Email universitaire').fill(LUKA_EMAIL);
  await page.getByRole('button', { name: 'Envoyer' }).click();

  await expect(page.getByText(/un lien vous a été envoyé/i)).toBeVisible();

  // 2. Mail Mailpit → extraction du lien /reset-password?token=...
  const message = await waitForEmailTo(LUKA_EMAIL);
  const resetLink = await extractLinkMatching(
    message.ID,
    /https?:\/\/[^\s"'<>]+\/reset-password\?token=[^\s"'<>]+/i,
  );

  // 3. Clic sur le lien → un mdp temporaire s'affiche en clair sur la page
  await page.goto(resetLink);
  const passwordEl = page.locator('p.font-mono').first();
  await expect(passwordEl).toBeVisible({ timeout: 10_000 });
  const tempPassword = (await passwordEl.textContent())?.trim();
  expect(tempPassword).toBeTruthy();
  expect(tempPassword?.length).toBeGreaterThan(0);

  // 4. Login avec le nouveau mdp
  await loginAs(page, LUKA_EMAIL, tempPassword ?? '');

  const me = await page.request.get('/api/auth/me');
  expect(me.ok()).toBeTruthy();
});
