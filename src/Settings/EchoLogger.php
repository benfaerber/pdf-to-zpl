<?php

namespace Faerber\PdfToZpl\Settings;

use Exception;
use Psr\Log\LoggerInterface;
use Stringable;

/** 
 * A simple default logger for those who just want
* a non-framework logger that uses echo to log.
*/
class EchoLogger implements LoggerInterface {
    public function log($level, string|\Stringable $message, array $context = []): void {
        if ($level instanceof Stringable || ! is_string($level)) {
            throw new Exception("Level must be a string!");
        } 
        echo "[{$level}] {$message}" . ($context ? " (Context: " . json_encode($context) . ")" : "") . "\n";
    }

    /**
     * System is unusable.
     *
     * @param mixed[] $context
     */
    public function emergency(string|\Stringable $message, array $context = []): void {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param mixed[] $context
     */
    public function alert(string|\Stringable $message, array $context = []): void {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param mixed[] $context
     */
    public function critical(string|\Stringable $message, array $context = []): void {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param mixed[] $context
     */
    public function error(string|\Stringable $message, array $context = []): void {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param mixed[] $context
     */
    public function warning(string|\Stringable $message, array $context = []): void {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param mixed[] $context
     */
    public function notice(string|\Stringable $message, array $context = []): void {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param mixed[] $context
     */
    public function info(string|\Stringable $message, array $context = []): void {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param mixed[] $context
     */
    public function debug(string|\Stringable $message, array $context = []): void {
        $this->log(__FUNCTION__, $message, $context);
    }
}
