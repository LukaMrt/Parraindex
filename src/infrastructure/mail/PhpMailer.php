<?php

namespace App\infrastructure\mail;

use App\application\logging\Logger;
use App\application\mail\Mailer;
use Exception;
use PHPMailer\PHPMailer\SMTP;

class PhpMailer implements Mailer
{
    private Logger $logger;
    private \PHPMailer\PHPMailer\PHPMailer $mailer;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);

        $this->mailer->SMTPDebug = $_ENV['DEBUG'] === "true" ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
        $this->mailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['MAIL_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['MAIL_USERNAME'];
        $this->mailer->Password = $_ENV['MAIL_PASSWORD'];
        $this->mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $_ENV['MAIL_PORT'];
        $this->mailer->Encoding = 'base64';
        $this->mailer->CharSet = 'UTF-8';
    }

    public function send(string $to, string $subject, string $body)
    {

        try {
            $this->mailer->setFrom($_ENV['MAIL_USERNAME'], 'ParrainBoss');
            $this->mailer->addAddress($to);
            $this->mailer->addReplyTo($_ENV['MAIL_USERNAME'], 'Ne pas répondre');

            $this->mailer->isHTML();
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            $this->mailer->send();

            $this->logger->info(PhpMailer::class, "Mailer success: [{$subject}] request, has been sent to {$to}");
        } catch (Exception) {
            $this->logger->error(PhpMailer::class, "Mailer error: {$this->mailer->ErrorInfo}");
            echo "Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}";
        }
    }
}
