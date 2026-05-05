<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Person\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

final readonly class AuthService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer,
    ) {
    }

    public function findUserByEmail(string $email): ?User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        return $user;
    }

    public function generateAndSendResetToken(User $user): ?ResetPasswordToken
    {
        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface) {
            return null;
        }

        $resetUrl = htmlspecialchars('/reset-password?token=' . $resetToken->getToken(), ENT_QUOTES);

        $email = (new Email())
            ->from(new Address('parraindex@parraindex.com', 'Parraindex'))
            ->to((string) $user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->html(
                '<h1>Bonjour !</h1>' .
                '<p>Pour réinitialiser votre mot de passe, cliquez sur le lien suivant :</p>' .
                '<p><a href="' . $resetUrl . '">Réinitialiser mon mot de passe</a></p>'
            );

        $this->mailer->send($email);

        return $resetToken;
    }

    public function generateFakeResetToken(): ResetPasswordToken
    {
        return $this->resetPasswordHelper->generateFakeResetToken();
    }

    public function validateTokenAndFetchUser(string $token): User
    {
        $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);

        return $user;
    }

    public function removeResetRequest(string $token): void
    {
        $this->resetPasswordHelper->removeResetRequest($token);
    }

    public function resetPassword(User $user, string $plainPassword): void
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $this->entityManager->flush();
    }
}
