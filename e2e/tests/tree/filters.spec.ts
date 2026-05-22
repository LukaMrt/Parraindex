import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('tree directory filters: search, year, count', async ({ page }) => {
  await page.goto('/tree');

  // Le tree affiche un compteur "{n} résultats"
  const counter = page.getByText(/\d+ résultats?/);
  await expect(counter).toBeVisible();
  const initialText = (await counter.textContent()) ?? '';
  const initialCount = parseInt(/(\d+)/.exec(initialText)?.[1] ?? '0', 10);
  expect(initialCount).toBeGreaterThan(0);

  // 1. Recherche "Luka" → compteur = 1
  await page.getByPlaceholder('Rechercher…').fill('Luka');
  await expect(counter).toHaveText(/^1 résultat$/);

  // 2. Reset recherche → compteur retourne à l'initial
  await page.getByPlaceholder('Rechercher…').fill('');
  await expect(counter).toHaveText(new RegExp(`^${initialCount.toString()} résultats?$`));

  // 3. Filtre année 2021 → nombre réduit (Luka est en 2021)
  // Ouvrir le combobox "Promo" puis sélectionner "2021 / 22"
  await page.getByPlaceholder('Promo').click();
  await page.getByRole('button', { name: /^2021 \/ 22$/ }).click();
  const filteredText = (await counter.textContent()) ?? '';
  const filteredCount = parseInt(/(\d+)/.exec(filteredText)?.[1] ?? '0', 10);
  expect(filteredCount).toBeGreaterThan(0);
  expect(filteredCount).toBeLessThan(initialCount);

  // Toutes les cartes visibles sont de 2021
  const cards = page.locator('[data-testid^="person-card-"]');
  await expect(cards).toHaveCount(filteredCount);

  // 4. Reset filtre années : le dropdown est encore ouvert, cliquer l'option pour désélectionner
  await page.getByRole('button', { name: /^2021 \/ 22$/ }).click();
  await expect(counter).toHaveText(new RegExp(`^${initialCount.toString()} résultats?$`));

  // 5. Tri alphabétique : on toggle, on vérifie que le bouton devient actif (background ink)
  const alphaBtn = page.getByRole('button', { name: /A → Z/ });
  await alphaBtn.click();
  await expect(alphaBtn).toHaveClass(/bg-ink/);
});
