import { createContext, useCallback, useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import type { Me, LoginRequest } from '../types/auth';
import * as authApi from '../lib/api/auth';
import { useNotification } from '../hooks/useNotification';

interface AuthContextValue {
  user: Me | null;
  isLoading: boolean;
  login: (credentials: LoginRequest) => Promise<{ ok: boolean; message?: string }>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
}

export const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<Me | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const { notify } = useNotification();

  useEffect(() => {
    authApi
      .getMe()
      .then((result) => {
        if (result.ok) {
          setUser(result.data);
        }
        setIsLoading(false);
      })
      .catch(() => {
        setIsLoading(false);
      });
  }, []);

  const login = useCallback(
    async (credentials: LoginRequest) => {
      const result = await authApi.login(credentials);
      if (result.ok) {
        setUser(result.data);
        notify('success', `Bienvenue, ${result.data.person.firstName} !`);
        return { ok: true };
      }
      return { ok: false, message: result.error.message };
    },
    [notify],
  );

  const logout = useCallback(async () => {
    await authApi.logout();
    setUser(null);
    notify('success', 'Vous avez été déconnecté.');
  }, [notify]);

  const refreshUser = useCallback(async () => {
    const result = await authApi.getMe();
    if (result.ok) setUser(result.data);
  }, []);

  return (
    <AuthContext.Provider value={{ user, isLoading, login, logout, refreshUser }}>
      {children}
    </AuthContext.Provider>
  );
}
