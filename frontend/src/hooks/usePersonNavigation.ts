import { useQueryClient } from '@tanstack/react-query';
import { useState } from 'react';
import { useNavigate } from 'react-router';
import { personQueries } from '../lib/queries';

export function usePersonNavigation() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [isPending, setIsPending] = useState(false);

  async function navigateTo(id: number) {
    setIsPending(true);
    await queryClient.query(personQueries.detail(id)).catch(() => undefined);
    void navigate(`/person/${id}`);
  }

  return { navigateTo, isPending };
}
