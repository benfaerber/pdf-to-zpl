<?php

use Faerber\PdfToZpl\ImageToZplConverter;
use Faerber\PdfToZpl\Logger\EchoLogger;
use Faerber\PdfToZpl\Settings\ConverterSettings;

/**
 * @Revs(2)
 * @Iterations(2)
 */
class BenchmarkPdfConversion {
    public static function testFile(string $name): string {
        return __DIR__ . "/../test_data/{$name}";
    }

    public static function settings(): ConverterSettings {
        return new ConverterSettings(
            logger: new EchoLogger(),
            verboseLogs: true,
        );
    } 

    private function convertFile(string $name, ConverterSettings $settings): void {
        $converter = new ImageToZplConverter(
            $settings
        );
        $testPath = self::testFile($name);
        $converter->convertFromFile($testPath);
    }

    /**
     * @Subject
     */
    public function doConvertGif(): void {
        $this->convertFile("ups-label-as-gif.gif", self::settings());
    }

    /**
     * @Subject
     */
    public function doConvertPng(): void {
        $this->convertFile("ups-label-as-png.png", self::settings());
    }


    /**
     * @Subject
     */
    public function doConvertDuck(): void {
        $this->convertFile("duck.png", self::settings());
    }
}
