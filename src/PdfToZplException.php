<?php

namespace Faerber\PdfToZpl;

use Exception;
use Throwable;

/**
 * Define a custom exception class
 */
class PdfToZplException extends Exception {
    public function __construct(string $message, int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
