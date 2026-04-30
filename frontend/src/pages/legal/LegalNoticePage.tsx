export function LegalNoticePage() {
  return (
    <div className="mx-auto max-w-4xl px-6 py-10">
      <h1 className="mb-8 text-3xl font-bold text-dark-blue">Mentions légales</h1>

      <div className="mb-8 grid gap-6 md:grid-cols-2">
        <article className="rounded-lg bg-white p-6 shadow-sm">
          <h2 className="mb-3 text-lg font-semibold text-dark-blue">{'Éditeur du site'}</h2>
          <address className="not-italic text-medium-blue">
            {"6 rue de l'Espoir, 69100 Villeurbanne"}
          </address>
          <ul className="mt-3 space-y-1 text-sm text-medium-blue">
            <li>
              <strong>{'Directeur de la publication :'}</strong> Luka Maret
            </li>
            <li>
              <strong>{'Téléphone :'}</strong>{' '}
              <a href="tel:0782390695" className="underline hover:text-light-blue">
                07 82 39 06 95
              </a>
            </li>
            <li>
              <strong>{'Courriel :'}</strong>{' '}
              <a href="mailto:contact@parraindex.com" className="underline hover:text-light-blue">
                contact@parraindex.com
              </a>
            </li>
            <li>
              <strong>{'Collaborateurs :'}</strong>
              <ul className="ml-4 mt-1 list-disc">
                <li>Luka MARET</li>
                <li>Lilian BAUDRY</li>
                <li>Melvyn DELPREE</li>
                <li>Vincent CHAVOT-DAMBRUN</li>
              </ul>
            </li>
          </ul>
        </article>

        <article className="rounded-lg bg-white p-6 shadow-sm">
          <h2 className="mb-3 text-lg font-semibold text-dark-blue">{'Hébergement du site'}</h2>
          <address className="not-italic text-medium-blue">
            {'14 rue Charles-V, 75004 Paris'}
          </address>
          <ul className="mt-3 space-y-1 text-sm text-medium-blue">
            <li>
              <strong>{'Société :'}</strong> BE1HOST
            </li>
            <li>
              <strong>{'Téléphone :'}</strong>{' '}
              <a href="tel:0972546363" className="underline hover:text-light-blue">
                09 72 54 63 63
              </a>
            </li>
            <li>
              <strong>{'Courriel :'}</strong>{' '}
              <a href="mailto:jordan@inovaperf.fr" className="underline hover:text-light-blue">
                jordan@inovaperf.fr
              </a>
            </li>
            <li>
              <strong>{'Numéro SIRET :'}</strong> 80142522400020
            </li>
          </ul>
        </article>
      </div>

      <div className="space-y-6">
        {[
          {
            title: 'Propriété intellectuelle',
            text: 'Le contenu de ce site est protégé par les lois relatives à la propriété intellectuelle et est la propriété exclusive des propriétaires de ce site. Il est interdit de reproduire, représenter, utiliser ou adapter, sous quelque forme que ce soit, tout ou partie de ce site sans une autorisation expresse.',
          },
          {
            title: 'Protection des données personnelles',
            text: 'Les informations personnelles que vous renseignez sur ce site sont traitées conformément au Règlement Général sur la Protection des Données (RGPD) et à la loi "Informatique et Libertés" du 6 janvier 1978. Pour exercer vos droits, contactez-nous à contact@parraindex.com.',
          },
          {
            title: 'Liens hypertextes',
            text: "Le site parraindex.com peut inclure des liens vers d'autres sites internet. Ces sites ne sont pas sous notre contrôle et nous déclinons toute responsabilité quant à leur contenu.",
          },
          {
            title: 'Responsabilité',
            text: 'En utilisant notre site web parraindex.com, vous reconnaissez que vous êtes seul responsable des risques liés à son utilisation. Les propriétaires de ce site ne peuvent pas être tenus responsables des dommages découlant de son utilisation.',
          },
        ].map(({ title, text }) => (
          <article key={title} className="rounded-lg bg-white p-6 shadow-sm">
            <h2 className="mb-2 text-lg font-semibold text-dark-blue">{title}</h2>
            <hr className="mb-3 border-light-grey" />
            <p className="text-medium-blue">{text}</p>
          </article>
        ))}
      </div>
    </div>
  );
}
