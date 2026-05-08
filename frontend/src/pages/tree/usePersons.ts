import { useQuery } from '@tanstack/react-query';
import { personQueries } from '../../lib/queries';
import type { PersonSummary } from '../../types/person';

interface PersonsState {
  persons: PersonSummary[];
  total: number | null;
  loading: boolean;
  loadingMore: boolean;
}

export function usePersons(): PersonsState {
  const { data: persons = [], isLoading } = useQuery(personQueries.tree());

  return {
    persons,
    total: persons.length,
    loading: isLoading,
    loadingMore: false,
  };
}
