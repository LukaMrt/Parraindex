<?php

namespace App\infrastructure\logging;

use App\application\logging\Logger;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;
use ZipArchive;

class MonologLogger implements Logger {

	private array $loggers = [];

	public function info(string $className, string $message, array $context = []) {
		$this->createLogger($className);
		$this->loggers[$className]->info($message, $context);
	}

	public function debug(string $className, string $message, array $context = []) {
		$this->createLogger($className);
		$this->loggers[$className]->debug($message, $context);
	}

	public function warning(string $className, string $message, array $context = []) {
		$this->createLogger($className);
		$this->loggers[$className]->warning($message, $context);
	}

	public function error(string $className, string $message, array $context = []) {
		$this->createLogger($className);
		$this->loggers[$className]->error($message, $context);
	}

	public function alert(string $className, string $message, array $context = []) {
		$this->createLogger($className);
		$this->loggers[$className]->alert($message, $context);
	}

	public function critical(string $className, string $message, array $context = []) {
		$this->createLogger($className);
		$this->loggers[$className]->critical($message, $context);
	}

	public function emergency(string $className, string $message, array $context = []) {
		$this->createLogger($className);
		$this->loggers[$className]->emergency($message, $context);
	}

	private function createLogger(string $className): void {

		if (!file_exists('../logs')) {
			mkdir('../logs');
		}

		if (!file_exists('../logs/archives')) {
			mkdir('../logs/archives');
		}

		if (!file_exists('../logs/logs.txt')) {
			touch('../logs/logs.txt');
		}

		if (!file_exists('../logs/errors.txt')) {
			touch('../logs/errors.txt');
		}

		$this->zipLoggFile('../logs/logs.txt', 'logs');
		$this->zipLoggFile('../logs/errors.txt', 'errors');
		$logger = new \Monolog\Logger($className);
		$logger->pushHandler(new StreamHandler('php://stdout', 100));
		$logger->pushHandler(new StreamHandler('../logs/logs.txt', 200));
		$logger->pushHandler(new StreamHandler('../logs/errors.txt', 400));
		$logger->pushHandler(new NativeMailerHandler('maret.luka@gmail.com', 'Error logged in the parraindex', 'contact@lukamaret.com', 550));
		$this->loggers[$className] = $logger;
	}

	private function zipLoggFile(string $logFile, string $name) {

		if (count(file($logFile)) < 1_000) {
			return;
		}

		$zip = new ZipArchive();
		$zipFileName = '../logs/archives/' . $name . '_' . date('Y-m-d_H-i-s') . '.zip';
		if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
			$zip->addFile($logFile, 'log.txt');
			$zip->close();
		}

		file_put_contents($logFile, '');
	}

}