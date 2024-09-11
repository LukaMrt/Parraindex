<?php

namespace App\Infrastructure\old\mail;

use App\Application\mail\Mailer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Throwable;

readonly class PhpMailer implements Mailer
{
    public function __construct(
        private LoggerInterface $logger,
        private MailerInterface $mailer
    ) {
    }

    public function send(string $to, string $subject, string $body): void
    {
        try {
            $email = (new Email())
                ->to($to)
                ->subject($subject)
                ->html($body);

            $this->mailer->send($email);

            $this->logger->info("Mailer success: [{$subject}] request, has been sent to {$to}");
        } catch (Throwable $e) {
            $this->logger->error("Mailer error: {$e->getMessage()}");
        }
    }
}
