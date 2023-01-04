<?php

namespace App\infrastructure\session;

use App\application\login\SessionManager;

/**
 * Default session manager to manage session
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
     * @param string $key Key of the session
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $_SESSION[$key];
    }


    /**
     * Set the session id
     * @param string $key Key of the session
     * @param $value Value of the session
     * @return void
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }


    /**
     * Verify if the session id exists
     * @param string $key Key of the session
     * @return bool
     */
    public function exists(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
}
