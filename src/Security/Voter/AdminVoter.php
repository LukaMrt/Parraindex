<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Person\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class AdminVoter extends Voter
{
    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        return $user instanceof User && $user->isAdmin();
    }
}
