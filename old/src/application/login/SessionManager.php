<?php

namespace App\application\login;

/**
 * Session service interface
 */
interface SessionManager
{
    /**
     * Starts a session
     * @return void
     */
    public function startSession(): void;


    /**
     * Destroys a session
     * @return void
     */
    public function destroySession(): void;


    /**
     * Checks if a session is active
     * @return bool true if a session is active, false otherwise
     */
    public function isSessionStarted(): bool;


    /**
     * Returns a session field
     * @param string $key the field key
     * @return mixed the field value
     */
    public function get(string $key): mixed;


    /**
     * Sets a session field
     * @param string $key the field key
     * @param mixed $value the field value
     * @return void
     */
    public function set(string $key, mixed $value): void;


    /**
     * Checks if a session field exists
     * @param string $key the field key
     * @return bool true if the field exists, false otherwise
     */
    public function exists(string $key): bool;
}
