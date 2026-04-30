import { describe, it, expect, vi, beforeEach } from 'vitest';
import { renderHook, act, waitFor } from '@testing-library/react';
import React from 'react';
import type { ReactNode } from 'react';
import { AuthProvider } from '../context/AuthContext';
import { useAuth } from './useAuth';

vi.mock('../lib/api/auth', () => ({
  getMe: vi.fn(),
  login: vi.fn(),
  logout: vi.fn(),
}));

import * as authApi from '../lib/api/auth';
import type { Me } from '../types/auth';
import type { PersonSummary } from '../types/person';

const mockPerson: PersonSummary = {
  id: 42,
  firstName: 'Alice',
  lastName: 'Dupont',
  fullName: 'Alice Dupont',
  startYear: 2020,
  picture: null,
  color: '#000',
};

const mockMe: Me = {
  id: 1,
  email: 'test@example.com',
  isAdmin: false,
  isVerified: true,
  person: mockPerson,
};

const wrapper = ({ children }: { children: ReactNode }) => <AuthProvider>{children}</AuthProvider>;

beforeEach(() => {
  vi.mocked(authApi.getMe).mockResolvedValue({
    ok: false,
    error: { code: 'UNAUTHORIZED', message: '', violations: {} },
  });
});

describe('useAuth', () => {
  it('lance une erreur si utilisé hors AuthProvider', () => {
    expect(() => {
      renderHook(() => useAuth());
    }).toThrow('useAuth must be used within an AuthProvider');
  });

  it('isLoading=true puis false après résolution de getMe', async () => {
    const { result } = renderHook(() => useAuth(), { wrapper });
    expect(result.current.isLoading).toBe(true);
    await waitFor(() => {
      expect(result.current.isLoading).toBe(false);
    });
  });

  it('user=null quand getMe retourne une erreur', async () => {
    const { result } = renderHook(() => useAuth(), { wrapper });
    await waitFor(() => {
      expect(result.current.isLoading).toBe(false);
    });
    expect(result.current.user).toBeNull();
  });

  it('user est défini quand getMe retourne un utilisateur', async () => {
    vi.mocked(authApi.getMe).mockResolvedValue({ ok: true, data: mockMe });
    const { result } = renderHook(() => useAuth(), { wrapper });
    await waitFor(() => {
      expect(result.current.isLoading).toBe(false);
    });
    expect(result.current.user).toEqual(mockMe);
  });

  it('login() met à jour user sur succès', async () => {
    vi.mocked(authApi.login).mockResolvedValue({ ok: true, data: mockMe });
    const { result } = renderHook(() => useAuth(), { wrapper });
    await waitFor(() => {
      expect(result.current.isLoading).toBe(false);
    });

    await act(async () => {
      const res = await result.current.login({ email: 'test@example.com', password: 'pass' });
      expect(res.ok).toBe(true);
    });
    expect(result.current.user).toEqual(mockMe);
  });

  it('login() retourne ok:false et message sur échec', async () => {
    vi.mocked(authApi.login).mockResolvedValue({
      ok: false,
      error: { code: 'UNAUTHORIZED', message: 'Mauvais mot de passe', violations: {} },
    });
    const { result } = renderHook(() => useAuth(), { wrapper });
    await waitFor(() => {
      expect(result.current.isLoading).toBe(false);
    });

    await act(async () => {
      const res = await result.current.login({ email: 'x@x.com', password: 'wrong' });
      expect(res.ok).toBe(false);
      expect(res.message).toBe('Mauvais mot de passe');
    });
    expect(result.current.user).toBeNull();
  });

  it('logout() remet user à null', async () => {
    vi.mocked(authApi.getMe).mockResolvedValue({ ok: true, data: mockMe });
    vi.mocked(authApi.logout).mockResolvedValue({ ok: true, data: null });
    const { result } = renderHook(() => useAuth(), { wrapper });
    await waitFor(() => {
      expect(result.current.user).toEqual(mockMe);
    });

    await act(async () => {
      await result.current.logout();
    });
    expect(result.current.user).toBeNull();
  });
});
