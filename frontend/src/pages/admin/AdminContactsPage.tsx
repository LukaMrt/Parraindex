import { useEffect, useState } from 'react';
import { closeContact, getAdminContacts, resolveContact } from '../../lib/api/admin';
import type { AdminContact } from '../../types/admin';
import type { ContactType } from '../../types/contact';

type FilterMode = 'all' | 'resolved' | ContactType;

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

const FILTER_OPTIONS: { value: FilterMode; label: string }[] = [
  { value: 'all', label: 'Nouveaux' },
  { value: 'ADD_PERSON', label: 'Ajouter personne' },
  { value: 'UPDATE_PERSON', label: 'Modifier personne' },
  { value: 'REMOVE_PERSON', label: 'Supprimer personne' },
  { value: 'ADD_SPONSOR', label: 'Ajouter parrainage' },
  { value: 'REMOVE_SPONSOR', label: 'Supprimer parrainage' },
  { value: 'OTHER', label: 'Autre' },
  { value: 'resolved', label: 'Résolus' },
];

const ACTION_CONFIG: Partial<Record<ContactType, { label: string; color: string }>> = {
  ADD_PERSON: { label: 'Créer', color: 'dark-green' },
  UPDATE_PERSON: { label: 'Éditer', color: 'dark-yellow' },
  REMOVE_PERSON: { label: 'Supprimer', color: 'dark-red' },
  ADD_SPONSOR: { label: 'Créer', color: 'dark-green' },
  REMOVE_SPONSOR: { label: 'Supprimer', color: 'dark-red' },
};

export function AdminContactsPage() {
  const [contacts, setContacts] = useState<AdminContact[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState<FilterMode>('all');

  useEffect(() => {
    void getAdminContacts().then((result) => {
      if (result.ok) setContacts(result.data);
      setLoading(false);
    });
  }, []);

  async function handleResolve(id: number) {
    const result = await resolveContact(id);
    if (result.ok)
      setContacts((prev) =>
        prev.map((c) => (c.id === id ? { ...c, resolutionDate: new Date().toISOString() } : c)),
      );
  }

  async function handleClose(id: number) {
    const result = await closeContact(id);
    if (result.ok) setContacts((prev) => prev.filter((c) => c.id !== id));
  }

  const visible = contacts.filter((c) => {
    if (filter === 'all') return c.resolutionDate === null;
    if (filter === 'resolved') return c.resolutionDate !== null;
    return c.type === filter && c.resolutionDate === null;
  });

  return (
    <div className="mx-auto max-w-6xl px-6 py-8">
      <h1 className="mb-8 text-2xl font-bold text-dark-blue">Demandes de contact</h1>

      <div className="flex gap-8">
        {/* Filtres */}
        <aside className="w-44 shrink-0">
          <h2 className="mb-3 text-sm font-semibold text-medium-blue">Filtres</h2>
          <div className="space-y-2">
            {FILTER_OPTIONS.map(({ value, label }) => (
              <label key={value} className="flex cursor-pointer items-center gap-2 text-sm">
                <input
                  type="radio"
                  name="filter"
                  value={value}
                  checked={filter === value}
                  onChange={() => {
                    setFilter(value);
                  }}
                  className="accent-dark-blue"
                />
                <span
                  className={filter === value ? 'font-semibold text-dark-blue' : 'text-medium-blue'}
                >
                  {label}
                </span>
              </label>
            ))}
          </div>
        </aside>

        {/* Liste */}
        <div className="flex-1">
          <p className="mb-4 text-sm text-medium-blue">{visible.length} demande(s)</p>

          {loading ? (
            <p className="text-medium-blue">Chargement…</p>
          ) : visible.length === 0 ? (
            <p className="text-medium-blue">Aucune demande.</p>
          ) : (
            <div className="space-y-4">
              {visible.map((contact) => {
                const action = ACTION_CONFIG[contact.type];
                return (
                  <article key={contact.id} className="rounded-lg bg-white p-5 shadow-sm">
                    <div className="flex items-start justify-between">
                      <h3 className="font-semibold text-dark-blue">{contact.typeLabel}</h3>
                      <span className="text-xs text-dark-grey">
                        {formatDate(contact.createdAt)}
                      </span>
                    </div>

                    <p className="mt-1 text-sm text-medium-blue">
                      de{' '}
                      <strong>
                        {contact.contacterFirstName} {contact.contacterLastName}
                      </strong>{' '}
                      <a
                        href={`mailto:${contact.contacterEmail}`}
                        className="underline hover:text-light-blue"
                      >
                        {contact.contacterEmail}
                      </a>
                    </p>

                    <hr className="my-3 border-light-grey" />

                    {(contact.relatedPersonFirstName ?? contact.relatedPersonLastName) && (
                      <div className="mb-2 text-sm text-dark-blue">
                        {contact.type === 'ADD_SPONSOR' || contact.type === 'REMOVE_SPONSOR' ? (
                          <>
                            <p>
                              <span className="font-semibold">Parrain :</span>{' '}
                              {contact.relatedPersonFirstName} {contact.relatedPersonLastName}
                            </p>
                            <p>
                              <span className="font-semibold">Fillot :</span>{' '}
                              {contact.relatedPerson2FirstName} {contact.relatedPerson2LastName}
                            </p>
                          </>
                        ) : (
                          <p>
                            <span className="font-semibold">Personne :</span>{' '}
                            {contact.relatedPersonFirstName} {contact.relatedPersonLastName}
                          </p>
                        )}
                      </div>
                    )}

                    <p className="text-sm text-dark-blue">
                      <span className="font-semibold">Message :</span> {contact.description}
                    </p>

                    <div className="mt-4 flex gap-2">
                      {contact.resolutionDate === null ? (
                        <>
                          {action && (
                            <button
                              onClick={() => {
                                void handleResolve(contact.id);
                              }}
                              className={`rounded px-3 py-1 text-xs font-medium text-white bg-${action.color} hover:opacity-90`}
                            >
                              {action.label}
                            </button>
                          )}
                          <button
                            onClick={() => {
                              void handleClose(contact.id);
                            }}
                            className="rounded border border-light-blue px-3 py-1 text-xs text-light-blue hover:bg-light-grey"
                          >
                            Clore
                          </button>
                        </>
                      ) : (
                        <span className="text-xs text-dark-grey">
                          Résolu le {formatDate(contact.resolutionDate)}
                        </span>
                      )}
                    </div>
                  </article>
                );
              })}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
