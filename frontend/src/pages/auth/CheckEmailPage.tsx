export function CheckEmailPage() {
  return (
    <div className="flex min-h-screen items-center justify-center bg-light-grey">
      <div className="max-w-md rounded-lg bg-white p-8 text-center shadow">
        <h1 className="mb-4 text-2xl font-bold text-dark-blue">Vérifiez votre email</h1>
        <p className="text-medium-blue">
          Un lien de confirmation a été envoyé à votre adresse email universitaire. Cliquez sur le
          lien pour activer votre compte.
        </p>
      </div>
    </div>
  );
}
