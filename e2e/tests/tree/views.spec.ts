import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('switch between grid, list and timeline views', async ({ page }) => {
  await page.goto('/tree');

  // Vue grid par défaut : au moins une PersonGridCard visible
  await expect(page.locator('[data-testid^="person-card-"]').first()).toBeVisible();

  // Bascule vers la vue Liste
  await page.getByRole('button', { name: 'Liste' }).click();
  await expect(page.getByTestId('view-list-container')).toBeVisible();
  await expect(page.locator('[data-testid^="person-card-"]')).toHaveCount(0);

  // Bascule vers la vue Timeline
  await page.getByRole('button', { name: 'Timeline' }).click();
  await expect(page.getByTestId('view-timeline-container')).toBeVisible();

  // Retour vers Grille
  await page.getByRole('button', { name: 'Grille' }).click();
  await expect(page.locator('[data-testid^="person-card-"]').first()).toBeVisible();
});
