import { Link } from 'react-router';

export function AboutPage() {
  return (
    <div className="mx-auto max-w-4xl px-6 py-10">
      <h1 className="mb-8 text-3xl font-bold text-dark-blue">À propos</h1>

      <div className="mb-10 flex flex-col items-center gap-8 md:flex-row">
        <img
          src="/images/icons/logo-blue.svg"
          alt="Logo Parraindex"
          className="h-32 w-32 shrink-0"
        />

        <div className="space-y-4 text-medium-blue">
          <p>
            {'Le Parraindex est un site web qui permet de trouver les'} <strong>parrains</strong> et
            les <strong>fillots</strong>{' '}
            {
              "de votre Université. À l'origine, il a été créé pour l'IUT Informatique de l'Université Lyon 1 mais nous souhaitons l'étendre à terme à d'autres universités."
            }
          </p>
          <p>
            {'Le Parraindex a pour but de'} <strong>recenser les liens de parrainage</strong>{' '}
            {
              'entre les étudiants, ce qui montre des liens fort. Le fait de voir son "arbre généalogique" permet de se rendre compte de la proximité des étudiants et de la qualité des liens de parrainage.'
            }
          </p>
          <p>
            {'Le Parraindex est un'} <strong>{'projet étudiant'}</strong>{' '}
            {"réalisé par des étudiants de l'IUT Informatique de l'Université Lyon 1."}
          </p>
          <p>
            {'Vous pouvez retrouver'}{' '}
            <Link to="/mentions-legales" className="font-semibold underline hover:text-light-blue">
              ici
            </Link>{' '}
            {'les mentions légales du site.'}
          </p>
        </div>
      </div>
    </div>
  );
}
