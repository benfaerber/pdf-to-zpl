<?php

namespace Faerber\PdfToZpl\Logger;

use Exception;
use Stringable;

/** 
 * A simple logger that prints colored output 
*/
class ColoredLogger extends BaseLogger {
    private static bool|null $noColor = null;
    private function isNoColor(): bool {
        return self::$noColor ??= getenv("NO_COLOR") !== false; 
    }

    private function colorCode(string $name): int {
        return match ($name) {
            "red", "error" => 31,
            "green", "notice" => 32,
            "yellow", "warning" => 33,
            "info" => 34,
            "blue" => 30,
            "magenta" => 35,
            "debug" => 35,
            default => 0,
        };
    }

    private function colored(string $message, string $color): string {
        if ($this->isNoColor()) {
            return $message;
        } 
        
        $colorCode = $this->colorCode($color); 
        return "\033[{$colorCode}m{$message}\033[0m";
    } 
    
    public function log($level, string|Stringable $message, array $context = []): void {
        if ($level instanceof Stringable) {
            $level = (string)$level;
        }

        if (! is_string($level)) {
            throw new Exception("Level must be a string!");
        }

        $contextMessage = $this->colored($context ? " (Context: " . json_encode($context) . ")" : "", "blue");
        $line = $this->colored("[{$level}]", $level)
            . " " . $this->colored("[pdf-to-zpl]", "yellow") 
            . " " . $message . $contextMessage . "\n"; 
        echo $line;
    }
}
