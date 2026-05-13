<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final class AdminEntryPoint implements AuthenticationEntryPointInterface
{
    #[\Override]
    public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
    {
        return new RedirectResponse('/login');
    }
}
