<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Person\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

readonly class LoginSuccessListener
{
    private FlashBagInterface $flashBag;

    public function __construct(
        private RequestStack $requestStack,
    ) {
        $this->flashBag = $this->requestStack->getSession()->getFlashBag();
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();

        if ($user->isVerified()) {
            $this->flashBag->add('success', 'Vous êtes connecté');
        } else {
            $this->flashBag->add('success', 'Vous êtes connecté, veuillez vérifier votre adresse email');
        }
    }
}
