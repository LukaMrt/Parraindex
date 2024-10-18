<?php

declare(strict_types=1);

namespace App\Application\logging;

/**
 * Log messages to a file, to the console, or both
 */
interface Logger
{
    /**
     * Log debug level message
     * @param string $className Name of the class that sends the log message
     * @param string $message Message to log
     * @param array $context Additional information to log
     */
    public function debug(string $className, string $message, array $context = []): void;


    /**
     * Log info level message
     * @param string $className Name of the class that sends the log message
     * @param string $message Message to log
     * @param array $context Additional information to log
     */
    public function info(string $className, string $message, array $context = []): void;


    /**
     * Log warning level message
     * @param string $className Name of the class that sends the log message
     * @param string $message Message to log
     * @param array $context Additional information to log
     */
    public function warning(string $className, string $message, array $context = []): void;


    /**
     * Log error level message
     * @param string $className Name of the class that sends the log message
     * @param string $message Message to log
     * @param array $context Additional information to log
     */
    public function error(string $className, string $message, array $context = []): void;


    /**
     * Log critical level message
     * @param string $className Name of the class that sends the log message
     * @param string $message Message to log
     * @param array $context Additional information to log
     */
    public function critical(string $className, string $message, array $context = []): void;


    /**
     * Log alert level message
     * @param string $className Name of the class that sends the log message
     * @param string $message Message to log
     * @param array $context Additional information to log
     */
    public function alert(string $className, string $message, array $context = []): void;


    /**
     * Log emergency level message
     * @param string $className Name of the class that sends the log message
     * @param string $message Message to log
     * @param array $context Additional information to log
     */
    public function emergency(string $className, string $message, array $context = []): void;
}
