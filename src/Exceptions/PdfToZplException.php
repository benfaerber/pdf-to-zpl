<?php

namespace Faerber\PdfToZpl\Exceptions;

use Exception;
use Throwable;

/** A custom exception to let you know this is a library related error */
class PdfToZplException extends Exception {
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        if (! is_int($this->code)) {
            $this->code = 0;
        }
        if (! is_string($this->message)) {
            $this->message = "Unknown error";
        }
        
        return __CLASS__ . ": {$this->message} ({$this->code})";
    }
}
