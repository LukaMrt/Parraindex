<?php

namespace App\application\logging;

interface Logger {

	public function debug(string $className, string $message, array $context = []);

	public function info(string $className, string $message, array $context = []);

	public function warning(string $className, string $message, array $context = []);

	public function error(string $className, string $message, array $context = []);

	public function critical(string $className, string $message, array $context = []);

	public function alert(string $className, string $message, array $context = []);

	public function emergency(string $className, string $message, array $context = []);

}