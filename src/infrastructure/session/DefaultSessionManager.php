<?php

namespace App\infrastructure\session;

use App\application\login\SessionManager;

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
     * Destroy the session
     * @return void
     */
    public function destroySession(): void
    {
        session_unset();
        session_destroy();
    }


    /**
     * Verify if the session is started
     * @return bool
     */
    public function isSessionStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }


    /**
     * Get the session id
     * @param string $key Key of the field
     * @return mixed Value of the field
     */
    public function get(string $key): mixed
    {
        return $_SESSION[$key];
    }


    /**
     * Set the session id
     * @param string $key Key of the session
     * @param mixed $value Value of the session
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }


    /**
     * Verify if the field exists
     * @param string $key Key of the field
     * @return bool True if the field exists, false otherwise
     */
    public function exists(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
}
