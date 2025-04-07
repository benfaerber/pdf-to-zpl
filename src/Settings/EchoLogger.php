<?php

namespace Faerber\PdfToZpl\Settings;

use Exception;
use Faerber\PdfToZpl\Images\{ImageProcessorOption, ImageProcessor};
use Psr\Log\LoggerInterface;

/** A simple echo logger so I don't have to download one for this package */
class EchoLogger implements LoggerInterface {
    public function emergency(string|\Stringable $message, array $context = []): void {
        $this->log('emergency', $message, $context); 
    }

    public function alert(string|\Stringable $message, array $context = []): void {
        $this->log('alert', $message, $context); 
    }

    public function critical(string|\Stringable $message, array $context = []): void {
        $this->log('critical', $message, $context); 
    }


    public function error(string|\Stringable $message, array $context = []): void {
        $this->log('error', $message, $context); 
    }


    public function warning(string|\Stringable $message, array $context = []): void {
        $this->log('warning', $message, $context); 
    }

    public function notice(string|\Stringable $message, array $context = []): void {
        $this->log('notice', $message, $context); 
    }

    public function info(string|\Stringable $message, array $context = []): void {
        $this->log('info', $message, $context); 
    }

    public function debug(string|\Stringable $message, array $context = []): void {
        $this->log('debug', $message, $context); 
    }

    public function log($level, string|\Stringable $message, array $context = []): void {
        echo "[{$level}] {$message}\n";
    }
}
