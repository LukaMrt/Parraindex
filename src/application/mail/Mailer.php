<?php

namespace App\application\mail;

/**
 * Mail interface for sending emails
 */
interface Mailer
{
    /**
     * Send an email
     * @param string $to Recipient email address
     * @param string $subject Subject of email
     * @param string $body Content of email
     * @return void
     */
    public function send(string $to, string $subject, string $body): void;
}
