<?php

use Faerber\PdfToZpl\ImageToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;

/**
 * @Revs(2)
 * @Iterations(2)
 */
class BenchmarkPdfConversion {
    public static function testFile(string $name): string {
        return __DIR__ . "/../test_data/{$name}";
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
        $this->convertFile("ups-label-as-gif.gif", new ConverterSettings());
    }

    /**
     * @Subject
     */
    public function doConvertPng(): void {
        $this->convertFile("ups-label-as-png.png", new ConverterSettings());
    }


    /**
     * @Subject
     */
    public function doConvertDuck(): void {
        $this->convertFile("duck.png", new ConverterSettings());
    }
}
