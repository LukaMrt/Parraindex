<?php

namespace App\application\mail;

interface Mailer
{

    public function send(string $to, string $subject, string $body);

}
