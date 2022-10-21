<?php

namespace App\model\database;

class DatabaseCredentials {

    private string $driver;
    private string $host;
    private int $port;
    private string $database;
    private string $username;
    private string $password;

    private function __construct() {
    }

    static function databaseCredentials(): DatabaseCredentials {
        return new DatabaseCredentials();
    }

    public function withDriver($driver): static {
        $this->driver = $driver;
        return $this;
    }

    public function withHost($host): static {
        $this->host = $host;
        return $this;
    }

    public function withPort($port): static {
        $this->port = $port;
        return $this;
    }

    public function withDatabase($database): static {
        $this->database = $database;
        return $this;
    }

    public function withUsername($username): static {
        $this->username = $username;
        return $this;
    }

    public function withPassword($password): static {
        $this->password = $password;
        return $this;
    }

    public function build(): DatabaseCredentials {
        return $this;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getDsn(): string {
        return "$this->driver:host=$this->host;port=$this->port;dbname=$this->database";
    }

}