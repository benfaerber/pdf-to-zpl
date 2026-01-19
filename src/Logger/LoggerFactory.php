<?php

declare(strict_types=1);

namespace Faerber\PdfToZpl\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\NullHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating Monolog loggers with various configurations.
 * Replaces the custom BaseLogger, ColoredLogger, EchoLogger, and VoidLogger implementations.
 */
class LoggerFactory {
    /**
     * Create a colored console logger (replaces ColoredLogger).
     * Respects NO_COLOR environment variable.
     */
    public static function createColoredLogger(string $name = 'pdf-to-zpl'): LoggerInterface {
        $logger = new Logger($name);
        $handler = new StreamHandler('php://stdout', Level::Debug);

        $noColor = getenv("NO_COLOR") !== false;

        // Create a custom formatter that handles colors
        $formatter = new class($noColor) extends LineFormatter {
            private bool $noColor;

            public function __construct(bool $noColor) {
                $this->noColor = $noColor;
                parent::__construct(null, null, false, true);
            }

            public function format(LogRecord $record): string {
                $levelNameRaw = $record['level_name'];
                $levelName = is_string($levelNameRaw) ? $levelNameRaw : 'UNKNOWN';
                $channel = $record['channel'] && is_string($record['channel']) ? $record['channel'] : 'DEBUG';
                $messageRaw = $record['message'];
                $message = is_string($messageRaw) ? $messageRaw : json_encode($messageRaw);
                $context = $record['context'] ?? [];

                if ($this->noColor) {
                    $contextStr = !empty($context) ? ' (Context: ' . json_encode($context) . ')' : '';
                    return "[{$levelName}] [{$channel}] {$message}{$contextStr}\n";
                }

                $colorCode = match ($levelName) {
                    'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => 31, // red
                    'WARNING' => 33, // yellow
                    'NOTICE' => 32, // green
                    'INFO' => 34, // blue
                    'DEBUG' => 35, // magenta
                    default => 0,
                };

                $contextStr = !empty($context) ? "\033[30m (Context: " . json_encode($context) . ")\033[0m" : '';
                return "\033[{$colorCode}m[{$levelName}]\033[0m \033[33m[{$channel}]\033[0m {$message}{$contextStr}\n";
            }
        };

        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * Create a simple console logger (replaces EchoLogger).
     * Outputs plain text without colors.
     */
    public static function createEchoLogger(string $name = 'pdf-to-zpl'): LoggerInterface {
        $logger = new Logger($name);
        $handler = new StreamHandler('php://stdout', Level::Debug);

        $formatter = new class extends LineFormatter {
            public function __construct() {
                parent::__construct(null, null, false, true);
            }

            public function format(LogRecord $record): string {
                $levelNameRaw = $record['level_name'];
                $levelName = is_string($levelNameRaw) ? $levelNameRaw : 'UNKNOWN';
                $channel = $record['channel'] && is_string($record['channel']) ? $record['channel'] : 'DEBUG';
                $messageRaw = $record['message'];
                $message = is_string($messageRaw) ? $messageRaw : json_encode($messageRaw);
                $context = $record['context'] ?? [];

                $contextStr = !empty($context) ? ' (Context: ' . json_encode($context) . ')' : '';
                return "[{$levelName}] [{$channel}] {$message}{$contextStr}\n";
            }
        };

        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * Create a null logger (replaces VoidLogger).
     * Discards all log messages.
     */
    public static function createVoidLogger(string $name = 'pdf-to-zpl'): LoggerInterface {
        $logger = new Logger($name);
        $logger->pushHandler(new NullHandler());

        return $logger;
    }

    /**
     * Create a logger instance based on the type.
     *
     * @param string $type One of: 'colored', 'echo', 'void'
     */
    public static function create(string $type = 'void', string $name = 'pdf-to-zpl'): LoggerInterface {
        return match ($type) {
            'colored' => self::createColoredLogger($name),
            'echo' => self::createEchoLogger($name),
            'void' => self::createVoidLogger($name),
            default => self::createVoidLogger($name),
        };
    }
}
