<?php

namespace App\Security\Voter;

use App\Entity\Person\Person;
use App\Entity\Person\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class PersonVoter extends Voter
{
    public const string PERSON_EDIT = 'PERSON_EDIT';

    public const string PERSON_DATA_DOWNLOAD = 'PERSON_DATA_DOWNLOAD';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::PERSON_EDIT, self::PERSON_DATA_DOWNLOAD], true)
            && $subject instanceof Person;
    }

    /**
     * @param string $attribute
     * @param Person $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::PERSON_EDIT, self::PERSON_DATA_DOWNLOAD => $subject->getId() === $user->getPerson()?->getId(),
            default => false,
        };
    }
}
