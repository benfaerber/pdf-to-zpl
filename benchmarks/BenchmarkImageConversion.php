<?php

declare(strict_types=1);

use Faerber\PdfToZpl\ImageToZplConverter;
use Faerber\PdfToZpl\Logger\LoggerFactory;
use Faerber\PdfToZpl\Settings\ConverterSettings;

use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Subject;

#[Revs(2)]
#[Iterations(2)]
class BenchmarkPdfConversion {
    public static function testFile(string $name): string {
        return __DIR__ . "/../test_data/{$name}";
    }

    public static function settings(): ConverterSettings {
        return new ConverterSettings(
            logger: LoggerFactory::createEchoLogger(),
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

    #[Subject] 
    public function doConvertGif(): void {
        $this->convertFile("ups-label-as-gif.gif", self::settings());
    }

    #[Subject] 
    public function doConvertPng(): void {
        $this->convertFile("ups-label-as-png.png", self::settings());
    }

    #[Subject] 
    public function doConvertDuck(): void {
        $this->convertFile("duck.png", self::settings());
    }
}
