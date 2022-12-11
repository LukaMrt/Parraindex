<?php

namespace App\infrastructure\mail;

use App\application\mail\Mailer;
use Exception;
use PHPMailer\PHPMailer\SMTP;

class PhpMailer implements Mailer {

	private \PHPMailer\PHPMailer\PHPMailer $mailer;

	public function __construct() {
		$this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);

		$this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
		$this->mailer->isSMTP();
		$this->mailer->Host = $_ENV['MAIL_HOST'];
		$this->mailer->SMTPAuth = true;
		$this->mailer->Username = $_ENV['MAIL_USERNAME'];
		$this->mailer->Password = $_ENV['MAIL_PASSWORD'];
		$this->mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
		$this->mailer->Port = 465;
		$this->mailer->Encoding = 'base64';
		$this->mailer->CharSet = 'UTF-8';
	}

	public function send(string $to, string $subject, string $body) {

		try {
			$this->mailer->setFrom('contact@lukamaret.com', 'Parraindex');
			$this->mailer->addAddress($to);
			$this->mailer->addReplyTo('contact@lukamaret.com', 'Ne pas répondre');

			$this->mailer->isHTML();
			$this->mailer->Subject = $subject;
			$this->mailer->Body = $body;

			$this->mailer->send();

		} catch (Exception) {
			echo "Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}";
		}

	}

}