<?php

namespace Faerber\PdfToZpl;

use Exception;
use Throwable;

/** A custom exception to let you know this is a library related error */
class PdfToZplException extends Exception {
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
