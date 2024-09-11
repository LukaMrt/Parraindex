<?php

namespace App\Application\mail;

interface Mailer
{
    public function send(string $to, string $subject, string $body): void;
}
