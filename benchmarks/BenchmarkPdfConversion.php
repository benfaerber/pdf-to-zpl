<?php

use Faerber\PdfToZpl\Images\ImageProcessorOption;
use Faerber\PdfToZpl\Logger\LoggerFactory;
use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;

use PhpBench\Attributes\{Revs, Iterations, Subject};

#[Revs(3)]
#[Iterations(2)]
class BenchmarkPdfConversion {
    public static function testFile(string $name): string {
        return __DIR__ . "/../test_data/{$name}";
    }

    private function convertFile(string $name, ConverterSettings $settings): void {
        $converter = new PdfToZplConverter(
            $settings
        );
        $testPath = self::testFile($name);
        $converter->convertFromFile($testPath);
    }

    private function convertFileWithProcessor(string $name, ImageProcessorOption $imageProcessor): void {
        $this->convertFile($name, new ConverterSettings(
            imageProcessorOption: $imageProcessor,
            logger: LoggerFactory::createEchoLogger(),
        ));
    }

    #[Subject]
    public function doConvertLabelImagick(): void {
        $this->convertFileWithProcessor("endicia-shipping-label.pdf", ImageProcessorOption::Imagick);
    }

    #[Subject]
    public function doConvertLabelGd(): void {
        $this->convertFileWithProcessor("endicia-shipping-label.pdf", ImageProcessorOption::Gd);
    }

    #[Subject]
    public function doConvertDonkeyImagick(): void {
        $this->convertFileWithProcessor("donkey.pdf", ImageProcessorOption::Imagick);
    }

    #[Subject]
    public function doConvertDonkeyGd(): void {
        $this->convertFileWithProcessor("donkey.pdf", ImageProcessorOption::Gd);
    }

    #[Subject]
    public function doConvertAmericaImagick(): void {
        $this->convertFileWithProcessor("america.pdf", ImageProcessorOption::Imagick);
    }


    #[Subject]
    public function doConvertAmericaGd(): void {
        $this->convertFileWithProcessor("america.pdf", ImageProcessorOption::Gd);
    }

    #[Subject]
    public function doConvertTinyLabel(): void {
        $this->convertFile("endicia-shipping-label.pdf", new ConverterSettings(
            labelWidth: 150,
            labelHeight: 100,
        ));
    }
}
