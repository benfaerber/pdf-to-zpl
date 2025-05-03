<?php

namespace Faerber\PdfToZpl\Logger;

use Exception;
use Psr\Log\LoggerInterface;
use Stringable;

/** 
 * A simple default logger for those who just want
* a non-framework logger that uses echo to log.
*/
class EchoLogger extends BaseLogger implements LoggerInterface {
    public function log($level, string|\Stringable $message, array $context = []): void {
        if ($level instanceof Stringable || ! is_string($level)) {
            throw new Exception("Level must be a string!");
        } 
        echo "[{$level}] [pdf-to-zpl] {$message}" . ($context ? " (Context: " . json_encode($context) . ")" : "") . "\n";
    }
}
