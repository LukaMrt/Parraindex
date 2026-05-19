import { test, expect } from '@playwright/test';
import { resetFixtures, clearMailpit } from '../../helpers/fixtures';

test.beforeEach(() => {
  resetFixtures();
  clearMailpit();
});

test('unauthenticated user accessing /person/:id/edit is redirected to /login', async ({
  page,
}) => {
  await page.goto('/person/1/edit');
  await expect(page).toHaveURL(/\/login$/, { timeout: 10_000 });
});
