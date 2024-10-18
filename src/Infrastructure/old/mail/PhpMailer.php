<?php

declare(strict_types=1);

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

    #[\Override]
    public function send(string $to, string $subject, string $body): void
    {
        try {
            $email = (new Email())
                ->to($to)
                ->subject($subject)
                ->html($body);

            $this->mailer->send($email);

            $this->logger->info(sprintf('Mailer success: [%s] request, has been sent to %s', $subject, $to));
        } catch (Throwable $throwable) {
            $this->logger->error('Mailer error: ' . $throwable->getMessage());
        }
    }
}
