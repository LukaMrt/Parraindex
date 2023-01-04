<?php

namespace App\application\mail;

/**
 * Send emails
 */
interface Mailer
{
    /**
     * Send email
     * @param string $to Email address
     * @param string $subject Subject
     * @param string $body Body
     * @return mixed
     */
    public function send(string $to, string $subject, string $body);
}
