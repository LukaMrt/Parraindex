<?php

namespace App\infrastructure\redirect;

use App\application\redirect\Redirect;
use App\infrastructure\router\Router;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class HttpRedirect implements Redirect {

	private Router $router;
	private PHPMailer $mailer;

	public function __construct(Router $router) {
		$this->router = $router;
		$this->mailer = new PHPMailer(true);

		$this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
		$this->mailer->isSMTP();
		$this->mailer->Host = $_ENV['MAIL_HOST'];
		$this->mailer->SMTPAuth = true;
		$this->mailer->Username = $_ENV['MAIL_USERNAME'];
		$this->mailer->Password = $_ENV['MAIL_PASSWORD'];
		$this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$this->mailer->Port = 465;
	}

    public function redirect(string $url): void {

		try {
			$this->mailer->setFrom('contact@lukamaret.com', 'Parraindex');
			$this->mailer->addAddress('maret.luka@gmail.com', 'Luka Maret');
			$this->mailer->addReplyTo('contact@lukamaret.com', 'Ne pas répondre');

			$this->mailer->isHTML();
			$this->mailer->Subject = 'Here is the subject';
			$this->mailer->Body = 'This is the HTML message body <b>in bold!</b>';
			$this->mailer->AltBody = 'This is the body in plain text for non-HTML mail clients';

			$this->mailer->send();

		} catch (Exception) {
			echo "Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}";
		}

		header('Location: ' . $this->router->url($url));
	}

}