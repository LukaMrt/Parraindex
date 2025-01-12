<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Person\User;
use App\Entity\Sponsor\Sponsor;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Sponsor>
 */
final class SponsorVoter extends Voter
{
    public const string EDIT = 'RIGHT_SPONSOR_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EDIT && $subject instanceof Sponsor;
    }

    /**
     * @param ?Sponsor $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User || !$subject instanceof Sponsor) {
            return false;
        }

        if ($attribute === self::EDIT) {
            if ($subject->getGodChild()->equals($user->getPerson())) {
                return true;
            }

            return $subject->getGodFather()->equals($user->getPerson());
        }

        return false;
    }
}
