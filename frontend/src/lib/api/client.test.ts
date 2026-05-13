// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { get, post, put, del, postFormData } from './client';

function mockFetch(status: number, body: unknown): void {
  vi.stubGlobal(
    'fetch',
    vi.fn().mockResolvedValue({
      ok: status >= 200 && status < 300,
      status,
      json: () => Promise.resolve(body),
    }),
  );
}

beforeEach(() => {
  vi.unstubAllGlobals();
  Object.defineProperty(document, 'cookie', { value: '', writable: true, configurable: true });
});

describe('client HTTP — cas nominaux', () => {
  it('get() retourne les données sur 200', async () => {
    mockFetch(200, { data: { id: 1 } });
    const result = await get<{ id: number }>('/api/test');
    expect(result).toEqual({ ok: true, data: { id: 1 } });
  });

  it('post() envoie le body et retourne les données', async () => {
    mockFetch(200, { data: { created: true } });
    const result = await post<{ created: boolean }>('/api/test', { name: 'foo' });
    expect(result).toEqual({ ok: true, data: { created: true } });
    const fetchCall = vi.mocked(fetch).mock.calls[0];
    expect(fetchCall?.[1]?.body).toBe(JSON.stringify({ name: 'foo' }));
  });

  it('put() envoie METHOD=PUT', async () => {
    mockFetch(200, { data: null });
    await put('/api/test', { x: 1 });
    expect(vi.mocked(fetch).mock.calls[0]?.[1]?.method).toBe('PUT');
  });

  it('del() envoie METHOD=DELETE', async () => {
    mockFetch(204, null);
    const result = await del('/api/test');
    expect(result).toEqual({ ok: true, data: null });
    expect(vi.mocked(fetch).mock.calls[0]?.[1]?.method).toBe('DELETE');
  });

  it('retourne ok:true avec data:null sur 204', async () => {
    mockFetch(204, null);
    const result = await del('/api/test');
    expect(result.ok).toBe(true);
  });
});

describe("client HTTP — gestion d'erreurs", () => {
  it("retourne ok:false avec l'erreur sur 400", async () => {
    mockFetch(400, { error: { code: 'VALIDATION_ERROR', message: 'Invalid', violations: {} } });
    const result = await get('/api/test');
    expect(result.ok).toBe(false);
    if (!result.ok) {
      expect(result.error.code).toBe('VALIDATION_ERROR');
    }
  });

  it('retourne ok:false sur 401', async () => {
    mockFetch(401, { error: { code: 'UNAUTHORIZED', message: 'Non autorisé', violations: {} } });
    const result = await get('/api/test');
    expect(result.ok).toBe(false);
  });

  it('retourne ok:false sur 404', async () => {
    mockFetch(404, { error: { code: 'PERSON_NOT_FOUND', message: 'Not found', violations: {} } });
    const result = await get('/api/test');
    expect(result.ok).toBe(false);
    if (!result.ok) {
      expect(result.error.code).toBe('PERSON_NOT_FOUND');
    }
  });
});

describe('client HTTP — header CSRF', () => {
  it('envoie X-XSRF-TOKEN depuis le cookie', async () => {
    Object.defineProperty(document, 'cookie', {
      value: 'XSRF-TOKEN=abc123',
      writable: true,
      configurable: true,
    });
    mockFetch(200, { data: null });
    await get('/api/test');
    const headers = vi.mocked(fetch).mock.calls[0]?.[1]?.headers as Record<string, string>;
    expect(headers['X-XSRF-TOKEN']).toBe('abc123');
  });

  it('envoie X-XSRF-TOKEN vide si cookie absent', async () => {
    mockFetch(200, { data: null });
    await get('/api/test');
    const headers = vi.mocked(fetch).mock.calls[0]?.[1]?.headers as Record<string, string>;
    expect(headers['X-XSRF-TOKEN']).toBe('');
  });
});

describe('postFormData', () => {
  it('retourne les données sur succès', async () => {
    mockFetch(200, { data: { url: '/images/pictures/test.jpg' } });
    const form = new FormData();
    form.append('file', new Blob(['img']), 'test.jpg');
    const result = await postFormData<{ url: string }>('/api/upload', form);
    expect(result).toEqual({ ok: true, data: { url: '/images/pictures/test.jpg' } });
  });

  it('retourne ok:false sur erreur', async () => {
    mockFetch(422, {
      error: { code: 'VALIDATION_ERROR', message: 'Invalid file', violations: {} },
    });
    const result = await postFormData('/api/upload', new FormData());
    expect(result.ok).toBe(false);
  });
});
