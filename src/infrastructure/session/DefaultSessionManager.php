<?php

namespace App\infrastructure\session;

use App\application\login\SessionManager;

class DefaultSessionManager implements SessionManager {

    public function startSession(): void {
        session_start();
    }

    public function destroySession(): void {
        session_destroy();
    }

    public function isSessionStarted(): bool {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function get(string $key): mixed {
        return $_SESSION[$key];
    }

    public function set(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

	public function exists(string $key): bool {
		return isset($_SESSION[$key]);
	}

}