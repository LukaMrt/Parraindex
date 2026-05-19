import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('homepage → tree → person Luka', async ({ page }) => {
  await page.goto('/');

  await expect(page.getByRole('heading', { name: /annuaire des parrains/i })).toBeVisible();

  await page.getByRole('link', { name: /Explorer l['']annuaire/i }).click();
  await expect(page).toHaveURL(/\/tree$/);

  await expect(page.getByPlaceholder('Rechercher…')).toBeVisible();
  await page.getByPlaceholder('Rechercher…').fill('Luka');

  const lukaCard = page.locator('[data-testid^="person-card-"]', { hasText: /luka/i }).first();
  await expect(lukaCard).toBeVisible();
  await lukaCard.click();

  await expect(page).toHaveURL(/\/person\/\d+/);
  await expect(page.getByRole('heading', { level: 1 })).toContainText(/luka\s+maret/i);
});
