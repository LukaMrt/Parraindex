<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Person\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class AuthService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer,
        private string $mailUser,
        private string $mailName,
    ) {
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    public function generateRandomPasswordAndNotify(User $user): void
    {
        $chars    = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%';
        $password = '';
        for ($i = 0; $i < 12; ++$i) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->entityManager->flush();

        $safePassword = htmlspecialchars($password, ENT_QUOTES);

        $email = new Email()
            ->from(new Address($this->mailUser, $this->mailName))
            ->to((string) $user->getEmail())
            ->subject('Votre nouveau mot de passe temporaire')
            ->html(
                '<h1>Réinitialisation de mot de passe</h1>' .
                '<p>Votre nouveau mot de passe temporaire est :</p>' .
                '<p style="font-size:1.4em;font-weight:bold;letter-spacing:2px;">' . $safePassword . '</p>' .
                '<p>Connectez-vous avec ce mot de passe, puis changez-le depuis votre profil.</p>'
            );

        $this->mailer->send($email);
    }

    public function resetPassword(User $user, string $plainPassword): void
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $this->entityManager->flush();
    }
}
