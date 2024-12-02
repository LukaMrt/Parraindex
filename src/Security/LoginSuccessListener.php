<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Person\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

readonly class LoginSuccessListener
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        /** @var FlashBagAwareSessionInterface $session */
        $session = $this->requestStack->getSession();

        $flashBag = $session->getFlashBag();

        /** @var User $user */
        $user = $event->getUser();

        if ($user->isVerified()) {
            $flashBag->add('success', 'Vous êtes connecté');
        } else {
            $flashBag->add('success', 'Vous êtes connecté, veuillez vérifier votre adresse email');
        }
    }
}
