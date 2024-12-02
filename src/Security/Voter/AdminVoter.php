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
final class AdminVoter extends Voter
{
    /**
     * @param Person $subject
     */
    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    /**
     * @param Person $subject
     */
    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        return $user instanceof User && $user->isAdmin();
    }
}
