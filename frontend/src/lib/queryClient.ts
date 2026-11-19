import { QueryClient } from '@tanstack/react-query';
import { isNotFound } from './queries';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 2 * 60 * 1000,
      // Inutile de réessayer une ressource absente
      retry: (failureCount, error) => !isNotFound(error) && failureCount < 1,
    },
  },
});
