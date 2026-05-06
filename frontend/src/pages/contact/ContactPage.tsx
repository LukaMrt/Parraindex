import { useState } from 'react';
import type { SyntheticEvent } from 'react';
import { useAuth } from '../../hooks/useAuth';
import { submitContact } from '../../lib/api/contact';
import type { ContactType } from '../../types/contact';

const CONTACT_TYPES: { value: ContactType; label: string }[] = [
  { value: 'ADD_PERSON', label: 'Ajouter une personne' },
  { value: 'UPDATE_PERSON', label: 'Modifier une personne' },
  { value: 'REMOVE_PERSON', label: 'Supprimer une personne' },
  { value: 'ADD_SPONSOR', label: 'Ajouter un parrainage' },
  { value: 'REMOVE_SPONSOR', label: 'Supprimer un parrainage' },
  { value: 'OTHER', label: 'Autre' },
];

const PERSON_TYPES: ContactType[] = ['ADD_PERSON', 'UPDATE_PERSON', 'REMOVE_PERSON'];
const SPONSOR_TYPES: ContactType[] = ['ADD_SPONSOR', 'REMOVE_SPONSOR'];

export function ContactPage() {
  const { user } = useAuth();

  const [type, setType] = useState<ContactType>('ADD_PERSON');
  const [firstName, setFirstName] = useState(user?.person.firstName ?? '');
  const [lastName, setLastName] = useState(user?.person.lastName ?? '');
  const [email, setEmail] = useState(user?.email ?? '');
  const [relatedFirstName, setRelatedFirstName] = useState('');
  const [relatedLastName, setRelatedLastName] = useState('');
  const [related2FirstName, setRelated2FirstName] = useState('');
  const [related2LastName, setRelated2LastName] = useState('');
  const [entryYear, setEntryYear] = useState(new Date().getFullYear().toString());
  const [description, setDescription] = useState('');
  const [sending, setSending] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');

  const isPersonType = PERSON_TYPES.includes(type);
  const isSponsorType = SPONSOR_TYPES.includes(type);
  const readonly = user !== null;

  async function handleSubmit(e: SyntheticEvent) {
    e.preventDefault();
    setSending(true);
    setError('');

    const result = await submitContact({
      type,
      contacterFirstName: firstName,
      contacterLastName: lastName,
      contacterEmail: email,
      description,
      ...(isPersonType && {
        relatedPersonFirstName: relatedFirstName,
        relatedPersonLastName: relatedLastName,
        entryYear: Number(entryYear),
      }),
      ...(isSponsorType && {
        relatedPersonFirstName: relatedFirstName,
        relatedPersonLastName: relatedLastName,
        relatedPerson2FirstName: related2FirstName,
        relatedPerson2LastName: related2LastName,
      }),
    });

    if (result.ok) setSuccess(true);
    else setError(result.error.message);
    setSending(false);
  }

  if (success) {
    return (
      <div className="mx-auto max-w-xl px-6 py-16 text-center">
        <p className="text-2xl text-dark-green">✓</p>
        <p className="mt-2 text-dark-blue">Votre demande a bien été envoyée.</p>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-2xl px-6 py-10">
      <h1 className="mb-8 text-2xl font-bold text-dark-blue">Formulaire de contact</h1>

      {error && (
        <div className="mb-4 rounded bg-light-red px-4 py-2 text-sm text-dark-red">{error}</div>
      )}

      <form
        onSubmit={(e) => {
          void handleSubmit(e);
        }}
        className="space-y-5"
      >
        <div>
          <label className="mb-1 block text-sm font-semibold text-medium-blue">
            Objet du message
          </label>
          <select
            value={type}
            onChange={(e) => {
              setType(e.target.value as ContactType);
            }}
            className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue"
          >
            {CONTACT_TYPES.map((o) => (
              <option key={o.value} value={o.value}>
                {o.label}
              </option>
            ))}
          </select>
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="mb-1 block text-sm font-semibold text-medium-blue">
              Votre prénom
            </label>
            <input
              type="text"
              value={firstName}
              onChange={(e) => {
                setFirstName(e.target.value);
              }}
              readOnly={readonly}
              required
              className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue read-only:bg-light-grey"
            />
          </div>
          <div>
            <label className="mb-1 block text-sm font-semibold text-medium-blue">Votre nom</label>
            <input
              type="text"
              value={lastName}
              onChange={(e) => {
                setLastName(e.target.value);
              }}
              readOnly={readonly}
              required
              className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue read-only:bg-light-grey"
            />
          </div>
        </div>

        <div>
          <label className="mb-1 block text-sm font-semibold text-medium-blue">Votre email</label>
          <input
            type="email"
            value={email}
            onChange={(e) => {
              setEmail(e.target.value);
            }}
            readOnly={readonly}
            required
            className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue read-only:bg-light-grey"
          />
        </div>

        {isPersonType && (
          <>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="mb-1 block text-sm font-semibold text-medium-blue">
                  Prénom de la personne
                </label>
                <input
                  type="text"
                  value={relatedFirstName}
                  onChange={(e) => {
                    setRelatedFirstName(e.target.value);
                  }}
                  required
                  className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue"
                />
              </div>
              <div>
                <label className="mb-1 block text-sm font-semibold text-medium-blue">
                  Nom de la personne
                </label>
                <input
                  type="text"
                  value={relatedLastName}
                  onChange={(e) => {
                    setRelatedLastName(e.target.value);
                  }}
                  required
                  className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue"
                />
              </div>
            </div>
            <div>
              <label className="mb-1 block text-sm font-semibold text-medium-blue">
                {"Année d'entrée à l'IUT"}
              </label>
              <input
                type="number"
                value={entryYear}
                onChange={(e) => {
                  setEntryYear(e.target.value);
                }}
                min={1990}
                max={new Date().getFullYear() + 1}
                required
                className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue"
              />
            </div>
          </>
        )}

        {isSponsorType && (
          <>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="mb-1 block text-sm font-semibold text-medium-blue">
                  Prénom parrain
                </label>
                <input
                  type="text"
                  value={relatedFirstName}
                  onChange={(e) => {
                    setRelatedFirstName(e.target.value);
                  }}
                  required
                  className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue"
                />
              </div>
              <div>
                <label className="mb-1 block text-sm font-semibold text-medium-blue">
                  Nom parrain
                </label>
                <input
                  type="text"
                  value={relatedLastName}
                  onChange={(e) => {
                    setRelatedLastName(e.target.value);
                  }}
                  required
                  className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue"
                />
              </div>
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="mb-1 block text-sm font-semibold text-medium-blue">
                  Prénom fillot
                </label>
                <input
                  type="text"
                  value={related2FirstName}
                  onChange={(e) => {
                    setRelated2FirstName(e.target.value);
                  }}
                  required
                  className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue"
                />
              </div>
              <div>
                <label className="mb-1 block text-sm font-semibold text-medium-blue">
                  Nom fillot
                </label>
                <input
                  type="text"
                  value={related2LastName}
                  onChange={(e) => {
                    setRelated2LastName(e.target.value);
                  }}
                  required
                  className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue"
                />
              </div>
            </div>
          </>
        )}

        <div>
          <label className="mb-1 block text-sm font-semibold text-medium-blue">Message</label>
          <textarea
            value={description}
            onChange={(e) => {
              setDescription(e.target.value);
            }}
            required
            rows={5}
            className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue"
          />
        </div>

        <button
          type="submit"
          disabled={sending}
          className="w-full rounded bg-dark-blue py-2 text-sm text-white hover:bg-medium-blue disabled:opacity-60"
        >
          {sending ? 'Envoi…' : 'Envoyer'}
        </button>
      </form>
    </div>
  );
}
