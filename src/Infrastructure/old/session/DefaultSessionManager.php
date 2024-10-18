<?php

declare(strict_types=1);

namespace App\Infrastructure\old\session;

use App\Application\login\SessionManager;

/**
 * Default session manager to manage session. It uses PHP session.
 */
class DefaultSessionManager implements SessionManager
{
    #[\Override]
    public function startSession(): void
    {
        session_start();
    }


    #[\Override]
    public function destroySession(): void
    {
        session_unset();
        session_destroy();
    }


    /**
     * @return bool true if the session is started, false otherwise
     */
    #[\Override]
    public function isSessionStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }


    /**
     * @param string $key Key of the field
     * @return mixed Value of the field
     */
    #[\Override]
    public function get(string $key): mixed
    {
        return $_SESSION[$key];
    }


    /**
     * @param string $key Key of the field
     * @param mixed $value Value of the field
     */
    #[\Override]
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }


    /**
     * @param string $key Key of the field
     * @return bool True if the field exists, false otherwise
     */
    #[\Override]
    public function exists(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
}
