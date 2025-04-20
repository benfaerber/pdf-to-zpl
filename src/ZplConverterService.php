<?php

namespace Faerber\PdfToZpl;

/** A converter able to convert certain file types into ZPL */
interface ZplConverterService {
    /** 
    * Read and convert a file into a list of ZPL commands (1 per page) 
    * @return string[]
    */
    public function convertFromFile(string $filepath): array;

    /** 
    * Convert a raw blob of binary data into a list of ZPL commands (1 per page) 
    * @return string[]
    */
    public function convertFromBlob(string $rawData): array;

    /** 
    * Get a list of extensions that this converter can convert 
    * @return string[] 
    */
    public static function canConvert(): array;
}
