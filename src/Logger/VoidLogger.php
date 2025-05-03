<?php

namespace Faerber\PdfToZpl\Logger;

/** 
 * Just throw everything logged into the void!
*/
class VoidLogger extends BaseLogger {
    public function log($level, string|\Stringable $message, array $context = []): void {
    }
}
