<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Person\Person;
use App\Entity\Person\User;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private PersonRepository $personRepository,
        private EmailVerifier $emailVerifier,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    /**
     * Registers a user.
     *
     * If personId is provided, links to that person (isValidated = false, requires admin approval).
     * Otherwise, tries to auto-detect a matching Person from a university email (isValidated = true).
     *
     * @throws \RuntimeException if auto-detection fails or the person is already linked.
     */
    public function register(User $user, string $plainPassword, ?int $personId = null): void
    {
        if ($personId !== null) {
            $person = $this->personRepository->getById($personId);

            if (!$person instanceof Person) {
                throw new \RuntimeException('Personne introuvable');
            }

            if ($this->personRepository->hasLinkedUser($personId)) {
                throw new \RuntimeException('Cette personne est déjà liée à un compte');
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword))
                ->setPerson($person)
                ->setValidated(false)
                ->setCreatedAt(new \DateTimeImmutable());
        } else {
            $email = (string) $user->getEmail();
            $names = $this->extractNamesFromEmail($email);

            if ($names === null) {
                throw new \RuntimeException("Format d'email non universitaire. Veuillez sélectionner votre profil manuellement.");
            }

            $person = $this->personRepository->getByIdentity($names['firstName'], $names['lastName']);

            if (!$person instanceof Person) {
                throw new \RuntimeException('Aucune personne ne correspond à cet email universitaire. Veuillez sélectionner votre profil manuellement.');
            }

            if ($this->personRepository->hasLinkedUser($person->getId())) {
                throw new \RuntimeException('Un compte existe déjà pour cette personne');
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword))
                ->setPerson($person)
                ->setValidated(false)
                ->setCreatedAt(new \DateTimeImmutable());
        }

        $this->userRepository->update($user);
    }

    public function deleteByPersonId(int $personId): void
    {
        $user = $this->userRepository->findByPerson($personId);

        if ($user instanceof User) {
            $this->userRepository->delete($user);
        }
    }

    public function findByPersonId(int $personId): ?User
    {
        return $this->userRepository->findByPerson($personId);
    }

    /**
     * @throws \RuntimeException if the current password is wrong, email is invalid, or password is too weak.
     */
    public function updateCredentials(
        User $user,
        ?string $newEmail,
        ?string $currentPassword,
        ?string $newPassword,
        bool $skipCurrentPasswordCheck = false,
    ): void {
        $changingPassword = $newPassword !== null && $newPassword !== '';
        $changingEmail    = !in_array($newEmail, [null, '', $user->getEmail()], true);

        if (!$changingEmail && !$changingPassword) {
            return;
        }

        if (!$skipCurrentPasswordCheck) {
            if ($currentPassword === null || $currentPassword === '') {
                throw new \RuntimeException('Le mot de passe actuel est requis');
            }

            if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                throw new \RuntimeException('Mot de passe actuel incorrect');
            }
        }

        if ($changingEmail) {
            $user->setEmail($newEmail);
        }

        if ($changingPassword) {
            if (strlen($newPassword) < 6) {
                throw new \RuntimeException('Le mot de passe doit faire au moins 6 caractères');
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
        }

        $this->userRepository->update($user);
    }

    public function sendVerificationEmail(User $user): void
    {
        $this->emailVerifier->sendEmailConfirmation('api_auth_verify_email', $user);
    }

    public function verifyEmail(Request $request, User $user): void
    {
        $this->emailVerifier->handleEmailConfirmation($request, $user);
    }

    /**
     * @return array{firstName: string, lastName: string}|null
     */
    public function extractNamesFromEmail(string $email): ?array
    {
        if (!preg_match('/^([a-zA-Z-]+)\.([a-zA-Z-]+)@etu\.univ-lyon1\.fr$/', $email, $matches)) {
            return null;
        }

        return [
            'firstName' => ucfirst(strtolower($matches[1])),
            'lastName'  => ucfirst(strtolower($matches[2])),
        ];
    }
}
