<?php

namespace App\Infrastructure\session;

use App\Application\login\SessionManager;

/**
 * Default session manager to manage session. It uses PHP session.
 */
class DefaultSessionManager implements SessionManager
{
    /**
     * @return void
     */
    public function startSession(): void
    {
        session_start();
    }


    /**
     * @return void
     */
    public function destroySession(): void
    {
        session_unset();
        session_destroy();
    }


    /**
     * @return bool true if the session is started, false otherwise
     */
    public function isSessionStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }


    /**
     * @param string $key Key of the field
     * @return mixed Value of the field
     */
    public function get(string $key): mixed
    {
        return $_SESSION[$key];
    }


    /**
     * @param string $key Key of the field
     * @param mixed $value Value of the field
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }


    /**
     * @param string $key Key of the field
     * @return bool True if the field exists, false otherwise
     */
    public function exists(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
}
