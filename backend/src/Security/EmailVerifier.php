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
use Twig\Environment;

readonly class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private Environment $twig,
        private string $frontendUrl,
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

        $query     = parse_url($signatureComponents->getSignedUrl(), PHP_URL_QUERY) ?? '';
        $signedUrl = rtrim($this->frontendUrl, '/') . '/verify-email?' . $query;
        $firstName = $user->getPerson()?->getFirstName() ?? 'Nouvel utilisateur';

        $html = $this->twig->render('email/verify.html.twig', [
            'firstName' => $firstName,
            'signedUrl' => $signedUrl,
        ]);

        $email = new Email()
            ->from(new Address('parraindex@parraindex.com', 'Parraindex'))
            ->to((string) $user->getEmail())
            ->subject('Confirmez votre compte Parraindex')
            ->html($html);

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

        $user->setValidated(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
