<?php

use Faerber\PdfToZpl\Images\ImageProcessorOption;
use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;

/**
 * @Revs(2)
 * @Iterations(2)
 */
class BenchmarkPdfConversion
{
    public static function testFile(string $name): string
    {
        return __DIR__ . "/../test_data/{$name}";
    }

    private function convertFile(string $name, ConverterSettings $settings)
    {
        $converter = new PdfToZplConverter(
            $settings
        );
        $testPath = self::testFile($name);
        $converter->convertFromFile($testPath);
    }

    private function convertFileWithProcessor(string $name, ImageProcessorOption $imageProcessor)
    {
        return $this->convertFile($name, new ConverterSettings(
            imageProcessorOption: $imageProcessor,
        ));
    }

    /**
     * Verify phpbench is working properly
     * @Subject
     */
    public function doAdd()
    {
        $b = 1 + 2;
    }

    /**
     * @Subject
     */
    public function doConvertLabelImagick()
    {
        $this->convertFileWithProcessor("endicia-shipping-label.pdf", ImageProcessorOption::Imagick);
    }

    /**
     * @Subject
     */
    public function doConvertLabelGd()
    {
        $this->convertFileWithProcessor("endicia-shipping-label.pdf", ImageProcessorOption::Gd);
    }

    /**
     * @Subject
     */
    public function doConvertDonkeyImagick()
    {
        $this->convertFileWithProcessor("donkey.pdf", ImageProcessorOption::Imagick);
    }

    /**
     * @Subject
     */
    public function doConvertDonkeyGd()
    {
        $this->convertFileWithProcessor("donkey.pdf", ImageProcessorOption::Gd);
    }

    /**
     * @Subject
     */
    public function doConvertAmericaImagick()
    {
        $this->convertFileWithProcessor("america.pdf", ImageProcessorOption::Imagick);
    }


    /**
     * @Subject
     */
    public function doConvertAmericaGd()
    {
        $this->convertFileWithProcessor("america.pdf", ImageProcessorOption::Gd);
    }

    /**
     * @Subject
     */
    public function doConvertTinyLabel()
    {
        $this->convertFile("endicia-shipping-label.pdf", new ConverterSettings(
            labelWidth: 150,
            labelHeight: 100,
        ));
    }
}
