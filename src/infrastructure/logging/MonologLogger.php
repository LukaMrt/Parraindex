<?php

namespace App\infrastructure\logging;

use App\application\logging\Logger;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;
use ZipArchive;

/**
 * Send log
 */
class MonologLogger implements Logger
{
    /**
     * @var \Monolog\Logger $logger Logger instance
     */
    private const DEFAULT_LOG_FILE = '/log.txt';
    /**
     * @var \Monolog\Logger $logger Logger instance
     */
    private const ERROR_FILE = '/errors.txt';
    /**
     * @var array $logFiles Log files
     */
    private array $loggers = [];


    /**
     * info constructor
     * @param string $className Class name
     * @param string $message Message
     * @param array $context Context
     * @return void
     */
    public function info(string $className, string $message, array $context = []): void
    {
        $this->createLogger($className);
        $this->loggers[$className]->info($message, $context);
    }


    /**
     * Create a logger
     * @param string $className Class name
     * @return void
     */
    private function createLogger(string $className): void
    {

        $logPath = '../var/log';

        if (!file_exists($logPath)) {
            mkdir($logPath, 0777, true);
        }

        if (!file_exists($logPath . '/archives')) {
            mkdir($logPath . '/archives');
        }

        if (!file_exists($logPath . self::DEFAULT_LOG_FILE)) {
            touch($logPath . self::DEFAULT_LOG_FILE);
        }

        if (!file_exists($logPath . self::ERROR_FILE)) {
            touch($logPath . self::ERROR_FILE);
        }

        $this->zipLoggFile($logPath . self::DEFAULT_LOG_FILE, 'logs', $logPath);
        $this->zipLoggFile($logPath . self::ERROR_FILE, 'errors', $logPath);
        $logger = new \Monolog\Logger($className);
        $logger->pushHandler(new StreamHandler('php://stdout', 100));
        $logger->pushHandler(new StreamHandler($logPath . self::DEFAULT_LOG_FILE, 200));
        $logger->pushHandler(new StreamHandler($logPath . self::ERROR_FILE, 400));
        $logger->pushHandler(new NativeMailerHandler(
            'maret.luka@gmail.com',
            'Error logged in the parraindex',
            'contact@lukamaret.com',
            550
        ));
        $this->loggers[$className] = $logger;
    }


    /**
     * Zip the log file
     * @param string $logFile Log file
     * @param string $name Name of the zip file
     * @param string $logPath Log path
     * @return void
     */
    private function zipLoggFile(string $logFile, string $name, string $logPath): void
    {

        if (count(file($logFile)) < 1_000) {
            return;
        }

        $zip = new ZipArchive();
        $zipFileName = $logPath . '/archives/' . $name . '_' . date('Y-m-d_H-i-s') . '.zip';
        if ($zip->open($zipFileName, ZipArchive::CREATE) === true) {
            $zip->addFile($logFile, 'log.txt');
            $zip->close();
        }

        file_put_contents($logFile, '');
    }


    /**
     * Send a debug message
     * @param string $className Class name
     * @param string $message Message
     * @param array $context Context
     * @return void
     */
    public function debug(string $className, string $message, array $context = []): void
    {
        $this->createLogger($className);
        $this->loggers[$className]->debug($message, $context);
    }


    /**
     * Send a warning message
     * @param string $className Class name
     * @param string $message Message
     * @param array $context Context
     * @return void
     */
    public function warning(string $className, string $message, array $context = []): void
    {
        $this->createLogger($className);
        $this->loggers[$className]->warning($message, $context);
    }


    /**
     * Send a error message
     * @param string $className Class name
     * @param string $message Message
     * @param array $context Context
     * @return void
     */
    public function error(string $className, string $message, array $context = []): void
    {
        $this->createLogger($className);
        $this->loggers[$className]->error($message, $context);
    }


    /**
     * Send a alert message
     * @param string $className Class name
     * @param string $message Message
     * @param array $context Context
     * @return void
     */
    public function alert(string $className, string $message, array $context = []): void
    {
        $this->createLogger($className);
        $this->loggers[$className]->alert($message, $context);
    }


    /**
     * Send a critical message
     * @param string $className Class name
     * @param string $message Message
     * @param array $context Context
     * @return void
     */
    public function critical(string $className, string $message, array $context = []): void
    {
        $this->createLogger($className);
        $this->loggers[$className]->critical($message, $context);
    }


    /**
     * Send an emergency message
     * @param string $className Class name
     * @param string $message Message
     * @param array $context Context
     * @return void
     */
    public function emergency(string $className, string $message, array $context = []): void
    {
        $this->createLogger($className);
        $this->loggers[$className]->emergency($message, $context);
    }
}
