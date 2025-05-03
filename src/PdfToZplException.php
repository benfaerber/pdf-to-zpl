<?php

namespace Faerber\PdfToZpl;

use Exception;
use Throwable;

/** A custom exception to let you know this is a library related error */
class PdfToZplException extends Exception {
    /** @var array<array-key, mixed>|null $context */
    private $context; 
    
    /** @param array<array-key, mixed>|null $context */
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null, array|null $context = null) {
        $this->context = $context; 
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        if (! is_int($this->code)) {
            $this->code = 0;
        }
        if (! is_string($this->message)) {
            $this->message = "Unknown error";
        }
        
        return __CLASS__ . ": {$this->message} ({$this->code})" 
            . ($this->context ? " " . json_encode($this->context) : "");
    }
}