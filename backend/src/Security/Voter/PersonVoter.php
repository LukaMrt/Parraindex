<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Person\Person;
use App\Entity\Person\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Person>
 */
final class PersonVoter extends Voter
{
    public const string EDIT = 'RIGHT_PERSON_EDIT';

    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EDIT && $subject instanceof Person;
    }

    /**
     * @param Person $subject
     */
    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if (!$user->isValidated()) {
            return false;
        }

        return $subject->getId() === $user->getPerson()?->getId();
    }
}
