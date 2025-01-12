<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Person\Person;
use App\Entity\Person\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Person>
 */
final class PersonVoter extends Voter
{
    public const string EDIT = 'RIGHT_PERSON_EDIT';

    public const string DOWNLOAD_DATA = 'RIGHT_PERSON_DOWNLOAD_DATA';

    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DOWNLOAD_DATA], true)
            && $subject instanceof Person;
    }

    /**
     * @param ?Person $subject
     */
    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User || !$subject instanceof Person) {
            return false;
        }

        return match ($attribute) {
            self::EDIT, self::DOWNLOAD_DATA => $subject->getId() === $user->getPerson()?->getId(),
            default => false,
        };
    }
}
