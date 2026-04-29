<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Person\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

final readonly class AuthService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private UserPasswordHasherInterface $passwordHasher,
        private TranslatorInterface $translator,
        private MailerInterface $mailer,
    ) {
    }

    public function findUserByEmail(string $email): ?User
    {
        /** @var ?User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        return $user;
    }

    /**
     * Generates a reset token and sends the reset email. Returns null if token generation fails.
     */
    public function generateAndSendResetToken(User $user): ?ResetPasswordToken
    {
        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface) {
            return null;
        }

        $email = (new TemplatedEmail())
            ->from(new Address('parraindex@parraindex.com', 'Parraindex'))
            ->to((string) $user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context(['resetToken' => $resetToken]);

        $this->mailer->send($email);

        return $resetToken;
    }

    public function generateFakeResetToken(): ResetPasswordToken
    {
        return $this->resetPasswordHelper->generateFakeResetToken();
    }

    /**
     * @throws ResetPasswordExceptionInterface if the token is invalid or expired
     */
    public function validateTokenAndFetchUser(string $token): User
    {
        /** @var User $user */
        $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);

        return $user;
    }

    public function getTranslatedError(ResetPasswordExceptionInterface $e): string
    {
        return sprintf(
            '%s - %s',
            $this->translator->trans(
                ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE,
                [],
                'ResetPasswordBundle'
            ),
            $this->translator->trans($e->getReason(), [], 'ResetPasswordBundle')
        );
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
