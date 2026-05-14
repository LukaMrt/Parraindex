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
        private string $appSecret,
    ) {
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    public function sendResetLink(User $user, string $callbackUrl): void
    {
        $token    = $this->generateResetToken($user);
        $resetUrl = htmlspecialchars(rtrim($callbackUrl, '/') . '?token=' . $token, ENT_QUOTES);

        $email = new Email()
            ->from(new Address($this->mailUser, $this->mailName))
            ->to((string) $user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->html(
                '<h1>Réinitialisation de mot de passe</h1>' .
                '<p>Cliquez sur ce lien pour recevoir un nouveau mot de passe temporaire (valable 1 heure) :</p>' .
                '<p><a href="' . $resetUrl . '">Réinitialiser mon mot de passe</a></p>'
            );

        $this->mailer->send($email);
    }

    public function validateAndApplyRandomPassword(string $token): string
    {
        $data = $this->validateResetToken($token);

        /** @var User|null $user */
        $user = $this->entityManager->find(User::class, $data['userId']);

        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Utilisateur introuvable');
        }

        $password = $this->generateRandomPassword();

        // Hash the password in memory — only flush after, so a DB failure
        // doesn't leave the user with an unknown password.
        $hashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashed);
        $this->entityManager->flush();

        return $password;
    }

    public function resetPassword(User $user, string $plainPassword): void
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $this->entityManager->flush();
    }

    private function generateRandomPassword(): string
    {
        $chars    = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%';
        $password = '';
        for ($i = 0; $i < 12; ++$i) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }

    private function generateResetToken(User $user): string
    {
        $payload   = $user->getId() . '.' . (time() + 3600);
        $signature = hash_hmac('sha256', $payload, $this->appSecret);

        return rtrim(strtr(base64_encode($payload . '.' . $signature), '+/', '-_'), '=');
    }

    /**
     * @return array{userId: int}
     */
    private function validateResetToken(string $token): array
    {
        $padLen  = (4 - strlen($token) % 4) % 4;
        $decoded = base64_decode(str_pad(strtr($token, '-_', '+/'), strlen($token) + $padLen, '='), true);

        if ($decoded === false) {
            throw new \InvalidArgumentException('Token invalide');
        }

        $parts = explode('.', $decoded, 3);

        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Token invalide');
        }

        [
            $userId,
            $expiresAt,
            $signature,
        ]        = $parts;
        $payload = $userId . '.' . $expiresAt;
        $expectedSig = hash_hmac('sha256', $payload, $this->appSecret);

        if (!hash_equals($expectedSig, $signature)) {
            throw new \InvalidArgumentException('Token invalide');
        }

        if ((int) $expiresAt < time()) {
            throw new \InvalidArgumentException('Ce lien a expiré');
        }

        return ['userId' => (int) $userId];
    }
}
