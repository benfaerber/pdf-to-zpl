<?php

declare(strict_types=1);

use Faerber\PdfToZpl\Images\ImageProcessor;
use Faerber\PdfToZpl\Images\ImageProcessorOption;
use Faerber\PdfToZpl\Images\VipsProcessor;
use Faerber\PdfToZpl\LabelImage;
use Faerber\PdfToZpl\Logger\ColoredLogger;
use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use PHPUnit\Framework\TestCase;

final class CanConvertVipsTest extends TestCase {
    public function testCanConvertWithVips(): void {
        $settings = new ConverterSettings(
            logger: new ColoredLogger,
            verboseLogs: true,
            imageProcessorOption: ImageProcessorOption::Vips,
        );
    
        $settings->log("Testing vips");
        
        $utils = new TestUtils(new ColoredLogger);
        $converter = new PdfToZplConverter($settings);
        $pages = $converter->convertFromFile($utils->testData("endicia-shipping-label.pdf"));

        // foreach ($pages as $index => $page) {
        //     $img = new LabelImage($page);
        //     $img->saveAs($utils->testOutput("vips_{$index}.png"));
        // }
    }
}
