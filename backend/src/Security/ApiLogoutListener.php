<?php

declare(strict_types=1);

namespace App\Security;

use App\Api\ApiResponse;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener(event: LogoutEvent::class)]
final class ApiLogoutListener
{
    public function __invoke(LogoutEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $event->setResponse(ApiResponse::success(null));
    }
}
