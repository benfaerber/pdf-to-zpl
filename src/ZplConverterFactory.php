<?php

namespace Faerber\PdfToZpl;

use Exception;
use Faerber\PdfToZpl\ImageToZplConverter;
use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\ZplConverterService;

class ZplConverterFactory
{
    /** @var class-string<ZplConverterService>[] */
    const CONVERTER_SERVICES = [
        PdfToZplConverter::class, 
        ImageToZplConverter::class,
    ];

    public static function converterFromFile(string $filepath, ConverterSettings|null $settings = null): ZplConverterService
    {
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        $settings ??= new ConverterSettings();
        foreach (self::CONVERTER_SERVICES as $service) {
            if (in_array($ext, $service::canConvert())) {
                return new $service($settings);
            }
        }
        throw new Exception("No converter for {$ext} files!");
    }
}
