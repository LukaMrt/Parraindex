<?php

namespace App\application\logging;

interface Logger
{

    public function debug(string $className, string $message, array $context = []): void;


    public function info(string $className, string $message, array $context = []): void;


    public function warning(string $className, string $message, array $context = []): void;


    public function error(string $className, string $message, array $context = []): void;


    public function critical(string $className, string $message, array $context = []): void;


    public function alert(string $className, string $message, array $context = []): void;


    public function emergency(string $className, string $message, array $context = []): void;

}
