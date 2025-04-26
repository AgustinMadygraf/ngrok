<?php
// Declarar el modo estricto de PHP
declare(strict_types=1);

namespace App\Lib;

class Logger {
    private static ?Logger $instance = null;
    private string $logDirectory;
    private array $logLevels = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3
    ];
    private int $currentLogLevel;
    private int $maxFileSize = 5 * 1024 * 1024; // 5 MB por archivo
    private int $maxFiles = 7; // Mantener un historial de 7 archivos

    private function __construct(string $logDirectory) {
        $this->logDirectory = $logDirectory;
        if (!is_dir($this->logDirectory)) {
            mkdir($this->logDirectory, 0755, true);
        }
        $logLevel = getenv('LOG_LEVEL') ?: 'info';
        $this->currentLogLevel = $this->logLevels[strtolower($logLevel)] ?? $this->logLevels['info'];
    }

    public static function getInstance(string $logDirectory = __DIR__ . '/../logs'): Logger {
        if (self::$instance === null) {
            self::$instance = new Logger($logDirectory);
        }
        return self::$instance;
    }

    private function rotateLogs(string $logFile): void {
        if (file_exists($logFile) && filesize($logFile) >= $this->maxFileSize) {
            for ($i = $this->maxFiles - 1; $i > 0; $i--) {
                $oldFile = $logFile . '.' . $i;
                $newFile = $logFile . '.' . ($i + 1);
                if (file_exists($oldFile)) {
                    rename($oldFile, $newFile);
                }
            }
            rename($logFile, $logFile . '.1');
        }
    }

    public function log(string $level, string $message): void {
        if ($this->logLevels[strtolower($level)] < $this->currentLogLevel) {
            return; // Skip logging if the level is below the current log level
        }
        $date = new \DateTime();
        $logFile = $this->logDirectory . '/' . $date->format('Y-m-d') . '.log';
        $this->rotateLogs($logFile);
        $logMessage = sprintf("[%s] [%s]: %s\n", $date->format('Y-m-d H:i:s'), strtoupper($level), $message);
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    public function info(string $message): void {
        $this->log('info', $message);
    }

    public function warning(string $message): void {
        $this->log('warning', $message);
    }

    public function error(string $message): void {
        $this->log('error', $message);
    }

    public function debug(string $message): void {
        $this->log('debug', $message);
    }
}
