<?php

namespace Faerber\PdfToZpl;

use Faerber\PdfToZpl\Exceptions\PdfToZplException;
use Faerber\PdfToZpl\Settings\ConverterSettings;

/** A converter able to convert certain file types into ZPL */
interface ZplConverterService {
    /** 
    * Read and convert a file into a list of ZPL commands (1 per page) 
    * @throws PdfToZplException 
    * @return string[]
    */
    public function convertFromFile(string $filepath): array;

    /** 
    * Convert a raw blob of binary data into a list of ZPL commands (1 per page) 
    * @throws PdfToZplException
    * @return string[]
    */
    public function convertFromBlob(string $rawData): array;

    /** 
    * Get a list of extensions that this converter can convert 
    * @return string[] 
    */
    public static function canConvert(): array;

    /**
    * Create a new converter service.
    * This is preferred over the constructor as it can be verified via this interface.
    */
    public static function build(ConverterSettings $settings): static;
}
