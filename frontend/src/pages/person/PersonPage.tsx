import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router';
import { pictureUrl } from '../../lib/imageUrl';
import { promoColor } from '../../lib/colors';
import { getPerson } from '../../lib/api/persons';
import { useAuth } from '../../hooks/useAuth';
import type { Person } from '../../types/person';

export function PersonPage() {
  const { id } = useParams<{ id: string }>();
  const { user } = useAuth();
  const [person, setPerson] = useState<Person | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);

  useEffect(() => {
    if (!id) return;
    void getPerson(Number(id)).then((result) => {
      if (result.ok) setPerson(result.data);
      else setError(true);
      setLoading(false);
    });
  }, [id]);

  if (loading) return <div className="p-8 text-medium-blue">Chargement…</div>;
  if (error || !person) return <div className="p-8 text-medium-red">Personne introuvable.</div>;

  const canEdit = user !== null && (user.isAdmin || user.person.id === person.id);

  const endYear = person.startYear + (person.startYear < 2021 ? 2 : 3);

  return (
    <div className="mx-auto max-w-5xl px-6 py-8">
      <div className="flex flex-col gap-8 md:flex-row">
        {/* Famille */}
        <aside className="flex flex-col items-center gap-4">
          {person.godFathers.length > 0 && (
            <div className="flex flex-col items-center gap-2">
              <p className="text-sm font-semibold text-medium-blue">Parrains</p>
              <div className="flex gap-3">
                {person.godFathers.map((s) => (
                  <Link
                    key={s.id}
                    to={`/personne/${s.godFatherId}`}
                    className="flex flex-col items-center gap-1 text-center"
                  >
                    <div className="h-12 w-12 overflow-hidden rounded-full bg-medium-grey">
                      <img
                        src={pictureUrl(null)}
                        alt={s.godFatherName}
                        className="h-full w-full object-cover"
                      />
                    </div>
                    <span className="max-w-[5rem] text-xs text-dark-blue">{s.godFatherName}</span>
                  </Link>
                ))}
              </div>
            </div>
          )}

          {/* Photo principale */}
          <div
            className="h-36 w-36 overflow-hidden rounded-full shadow-md"
            style={{ backgroundColor: promoColor(person.startYear) }}
          >
            <img
              src={pictureUrl(person.picture)}
              alt={person.fullName}
              className="h-full w-full object-cover"
            />
          </div>

          {person.godChildren.length > 0 && (
            <div className="flex flex-col items-center gap-2">
              <div className="flex gap-3">
                {person.godChildren.map((s) => (
                  <Link
                    key={s.id}
                    to={`/personne/${s.godChildId}`}
                    className="flex flex-col items-center gap-1 text-center"
                  >
                    <div className="h-12 w-12 overflow-hidden rounded-full bg-medium-grey">
                      <img
                        src={pictureUrl(null)}
                        alt={s.godChildName}
                        className="h-full w-full object-cover"
                      />
                    </div>
                    <span className="max-w-[5rem] text-xs text-dark-blue">{s.godChildName}</span>
                  </Link>
                ))}
              </div>
              <p className="text-sm font-semibold text-medium-blue">Fillots</p>
            </div>
          )}
        </aside>

        {/* Description */}
        <div className="flex-1">
          <div className="mb-6">
            <h1 className="text-2xl font-bold text-dark-blue">
              {person.firstName} <span className="text-medium-blue">{person.lastName}</span>
            </h1>
            <p className="text-sm text-dark-grey">
              {person.startYear} / {endYear}
            </p>
          </div>

          {person.description && (
            <div className="mb-6">
              <h2 className="mb-2 text-sm font-semibold uppercase text-medium-blue">Biographie</h2>
              <p className="whitespace-pre-wrap text-dark-blue">{person.description}</p>
            </div>
          )}

          {person.characteristics.length > 0 && (
            <div className="flex flex-wrap gap-2">
              {person.characteristics
                .filter((c) => c.visible && c.value)
                .map((c) => (
                  <a
                    key={c.id}
                    href={c.typeUrl ? `${c.typeUrl}${c.value}` : undefined}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="rounded-full bg-light-grey px-3 py-1 text-sm text-dark-blue hover:bg-medium-grey"
                  >
                    {c.typeTitle} : {c.value}
                  </a>
                ))}
            </div>
          )}
        </div>
      </div>

      {/* Actions */}
      {canEdit && (
        <div className="mt-8 flex gap-4">
          <Link
            to={`/personne/${person.id}/modifier`}
            className="rounded bg-dark-blue px-5 py-2 text-sm text-white hover:bg-medium-blue"
          >
            Modifier le profil
          </Link>
          <a
            href={`/api/persons/${person.id}/export`}
            className="rounded border border-dark-blue px-5 py-2 text-sm text-dark-blue hover:bg-light-grey"
          >
            Télécharger mes données
          </a>
        </div>
      )}
    </div>
  );
}
