<?php

namespace App\application\login;

interface SessionManager {

    public function startSession(): void;

    public function destroySession(): void;

    public function isSessionStarted(): bool;

    public function get(string $key): mixed;

    public function set(string $key, $value): void;

	public function exists(string $key): bool;

}