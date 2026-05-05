<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Person\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

readonly class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function sendEmailConfirmation(string $verifyEmailRouteName, User $user): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            (string) $user->getId(),
            (string) $user->getEmail(),
            ['id' => $user->getId()]
        );

        $signedUrl = htmlspecialchars($signatureComponents->getSignedUrl(), ENT_QUOTES);

        $email = (new Email())
            ->from(new Address('parraindex@parraindex.com', 'Parraindex'))
            ->to((string) $user->getEmail())
            ->subject('Confirmez votre email')
            ->html(
                '<h1>Bonjour !</h1>' .
                '<p>Confirmez votre adresse email en cliquant sur le lien suivant :</p>' .
                '<p><a href="' . $signedUrl . '">Confirmer mon email</a></p>'
            );

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $transportException) {
            $this->logger->warning(
                'Email could not be sent to ' . $user->getEmail(),
                ['exception' => $transportException]
            );
        }
    }

    public function handleEmailConfirmation(Request $request, User $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
            $request,
            (string) $user->getId(),
            (string) $user->getEmail()
        );

        $user->setVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
