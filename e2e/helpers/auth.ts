import type { Page } from '@playwright/test';

export const LUKA_EMAIL = 'luka.maret@etu.univ-lyon1.fr';
export const LILIAN_EMAIL = 'lilian.baudry@etu.univ-lyon1.fr';
export const DEFAULT_PASSWORD = 'password';

/**
 * Log in via the UI (fills the form on /login and waits for the redirect to /).
 * Assumes the account exists and is validated (e.g. one of the UserFixture accounts).
 */
export async function loginAs(page: Page, email: string, password: string): Promise<void> {
  await page.goto('/login');
  await page.getByPlaceholder('Email').fill(email);
  await page.getByPlaceholder('Mot de passe').fill(password);
  await page.getByRole('button', { name: /se connecter/i }).click();
  await page.waitForURL(/\/$/, { timeout: 10_000 });
}

interface MeResponse {
  data: {
    id: number;
    email: string;
    isAdmin: boolean;
    isValidated: boolean;
    person: { id: number };
  };
}

/**
 * Returns the currently authenticated user data via /api/auth/me.
 * Uses page.request so cookies from the page session are reused.
 */
export async function getMe(page: Page): Promise<MeResponse['data']> {
  const response = await page.request.get('/api/auth/me');
  if (!response.ok()) {
    throw new Error(`/api/auth/me returned ${response.status().toString()}`);
  }
  const body = (await response.json()) as MeResponse;
  return body.data;
}
