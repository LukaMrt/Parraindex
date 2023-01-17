<?php

namespace App\infrastructure\logging;

use App\application\logging\Logger;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;
use ZipArchive;

/**
 * Monolog implementation of the Logger interface.
 */
class MonologLogger implements Logger
{
    /**
     * @var string The name of the log file
     */
    private const DEFAULT_LOG_FILE = '/log.txt';
    /**
     * @var string The name of the error log file
     */
    private const ERROR_FILE = '/errors.txt';


    /**
     * @param string $className Name of the class that sends the log message
     * @param string $message Message to log
     * @param array $context Additional information to log
     * @return void
     */
    public function info(string $className, string $message, array $context = []): void
    {
        $this->createLogger($className)->info($message, $context);
    }


    /**
     * Create logger for the given class
     * @param string $className Class name
     * @return \Monolog\Logger The logger to use for the class
     */
    private function createLogger(string $className): \Monolog\Logger
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

        $this->zipLoggFile($logPath . self::DEFAULT_LOG_FILE, 'logs', $logPath . 'logs');
        $this->zipLoggFile($logPath . self::ERROR_FILE, 'errors', $logPath . 'errors');
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
        return $logger;
    }


    /**
     * Zip the log file if it is bigger than 1 million lines
     * @param string $logFile Log file to watch
     * @param string $name Name of the zip folder
     * @param string $logPath Log path to store the zip file
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
     * @param string $className Name of the class that sends the log message
     * @param string $message Message to log
     * @param array $context Additional information to log
     * @return void
     */
    public function debug(string $className, string $message, array $context = []): void
    {
        $this->createLogger($className)->debug($message, $context);
    }


    /**
     * @param string $className Name of the class that sends the log message
     * @param string $message Message to log
     * @param array $context Additional information to log
     * @return void
     */
    public function warning(string $className, string $message, array $context = []): void
    {
        $this->createLogger($className)->warning($message, $context);
    }


    /**
     * @param string $className Name of the class that sends the log message
     * @param string $message Message to log
     * @param array $context Additional information to log
     * @return void
     */
    public function error(string $className, string $message, array $context = []): void
    {
        $this->createLogger($className)->error($message, $context);
    }


    /**
     * @param string $className Name of the class that sends the log message
     * @param string $message Message to log
     * @param array $context Additional information to log
     * @return void
     */
    public function alert(string $className, string $message, array $context = []): void
    {
        $this->createLogger($className)->alert($message, $context);
    }


    /**
     * @param string $className Name of the class that sends the log message
     * @param string $message Message to log
     * @param array $context Additional information to log
     * @return void
     */
    public function critical(string $className, string $message, array $context = []): void
    {
        $this->createLogger($className)->critical($message, $context);
    }


    /**
     * @param string $className Name of the class that sends the log message
     * @param string $message Message to log
     * @param array $context Additional information to log
     * @return void
     */
    public function emergency(string $className, string $message, array $context = []): void
    {
        $this->createLogger($className)->emergency($message, $context);
    }
}
