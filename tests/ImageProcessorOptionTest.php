<?php

declare(strict_types=1);

use Faerber\PdfToZpl\Images\ImageProcessorOption;
use Faerber\PdfToZpl\Images\ImageProcessor;
use Faerber\PdfToZpl\Images\ImagickProcessor;
use Faerber\PdfToZpl\Images\GdProcessor;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\ImageToZplConverter;
use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Logger\LoggerFactory;
use PHPUnit\Framework\TestCase;

final class ImageProcessorOptionTest extends TestCase {
    public function testImageProcessorOptionIsEnum(): void {
        $reflectionClass = new ReflectionClass(ImageProcessorOption::class);

        $this->assertTrue($reflectionClass->isEnum());
    }

    public function testImageProcessorOptionHasGdCase(): void {
        $this->assertTrue(enum_exists(ImageProcessorOption::class));
        $this->assertNotNull(ImageProcessorOption::Gd);
    }

    public function testImageProcessorOptionHasImagickCase(): void {
        $this->assertTrue(enum_exists(ImageProcessorOption::class));
        $this->assertNotNull(ImageProcessorOption::Imagick);
    }

    public function testGdCaseCreatesGdProcessor(): void {
        $settings = new ConverterSettings();
        $processor = ImageProcessorOption::Gd->processor($settings);

        $this->assertInstanceOf(ImageProcessor::class, $processor);
        $this->assertInstanceOf(GdProcessor::class, $processor);
    }

    public function testImagickCaseCreatesImagickProcessor(): void {
        $settings = new ConverterSettings();
        $processor = ImageProcessorOption::Imagick->processor($settings);

        $this->assertInstanceOf(ImageProcessor::class, $processor);
        $this->assertInstanceOf(ImagickProcessor::class, $processor);
    }

    public function testGdProcessorReturnsCorrectType(): void {
        $settings = new ConverterSettings(imageProcessorOption: ImageProcessorOption::Gd);
        $processor = $settings->imageProcessor;

        $this->assertEquals(ImageProcessorOption::Gd, $processor->processorType());
    }

    public function testImagickProcessorReturnsCorrectType(): void {
        $settings = new ConverterSettings(imageProcessorOption: ImageProcessorOption::Imagick);
        $processor = $settings->imageProcessor;

        $this->assertEquals(ImageProcessorOption::Imagick, $processor->processorType());
    }

    public function testProcessorMethodCreatesNewInstances(): void {
        $settings = new ConverterSettings();

        $processor1 = ImageProcessorOption::Gd->processor($settings);
        $processor2 = ImageProcessorOption::Gd->processor($settings);

        // Should create new instances each time
        $this->assertNotSame($processor1, $processor2);
    }

    public function testDifferentProcessorTypesCreateDifferentInstances(): void {
        $settings = new ConverterSettings();

        $gdProcessor = ImageProcessorOption::Gd->processor($settings);
        $imagickProcessor = ImageProcessorOption::Imagick->processor($settings);

        $this->assertNotSame($gdProcessor, $imagickProcessor);
        $this->assertInstanceOf(GdProcessor::class, $gdProcessor);
        $this->assertInstanceOf(ImagickProcessor::class, $imagickProcessor);
    }

    public function testProcessorAcceptsConverterSettings(): void {
        $customSettings = new ConverterSettings(
            labelWidth: 600,
            labelHeight: 800,
            dpi: 300
        );

        $gdProcessor = ImageProcessorOption::Gd->processor($customSettings);
        $imagickProcessor = ImageProcessorOption::Imagick->processor($customSettings);

        $this->assertInstanceOf(ImageProcessor::class, $gdProcessor);
        $this->assertInstanceOf(ImageProcessor::class, $imagickProcessor);
    }

    public function testConverterSettingsUsesProcessorOption(): void {
        $gdSettings = new ConverterSettings(imageProcessorOption: ImageProcessorOption::Gd);
        $imagickSettings = new ConverterSettings(imageProcessorOption: ImageProcessorOption::Imagick);

        $this->assertInstanceOf(GdProcessor::class, $gdSettings->imageProcessor);
        $this->assertInstanceOf(ImagickProcessor::class, $imagickSettings->imageProcessor);
    }

    public function testDefaultConverterSettingsUsesGd(): void {
        $settings = ConverterSettings::default();

        $this->assertInstanceOf(GdProcessor::class, $settings->imageProcessor);
        $this->assertEquals(ImageProcessorOption::Gd, $settings->imageProcessor->processorType());
    }

    public function testBothProcessorTypesImplementImageProcessor(): void {
        $settings = new ConverterSettings();

        $gdProcessor = ImageProcessorOption::Gd->processor($settings);
        $imagickProcessor = ImageProcessorOption::Imagick->processor($settings);

        $this->assertInstanceOf(ImageProcessor::class, $gdProcessor);
        $this->assertInstanceOf(ImageProcessor::class, $imagickProcessor);
    }

    public function testEnumCasesAreDistinct(): void {
        $this->assertNotEquals(ImageProcessorOption::Gd, ImageProcessorOption::Imagick);
    }

    public function testEnumCanBeUsedInMatch(): void {
        $result = match (ImageProcessorOption::Gd) {
            ImageProcessorOption::Gd => 'gd',
            ImageProcessorOption::Imagick => 'imagick',
        };

        $this->assertEquals('gd', $result);

        $result = match (ImageProcessorOption::Imagick) {
            ImageProcessorOption::Gd => 'gd',
            ImageProcessorOption::Imagick => 'imagick',
        };

        $this->assertEquals('imagick', $result);
    }

    public function testImagickProcessorCanConvertImage(): void {
        $utils = new TestUtils(LoggerFactory::createVoidLogger());
        $duck = $utils->testData("duck.png");

        $settings = new ConverterSettings(
            imageProcessorOption: ImageProcessorOption::Imagick,
            verboseLogs: false
        );

        $converter = new ImageToZplConverter($settings);
        $pages = $converter->convertFromFile($duck);

        $this->assertIsArray($pages);
        $this->assertCount(1, $pages);
        $this->assertIsString($pages[0]);
        $this->assertStringContainsString('^XA', $pages[0]); // ZPL start command
        $this->assertStringContainsString('^XZ', $pages[0]); // ZPL end command
        $this->assertStringContainsString('^GFA', $pages[0]); // ZPL graphic field command
    }

    public function testImagickProcessorCanConvertPdf(): void {
        $utils = new TestUtils(LoggerFactory::createVoidLogger());
        $pdf = $utils->testData("endicia-shipping-label.pdf");

        $settings = new ConverterSettings(
            imageProcessorOption: ImageProcessorOption::Imagick,
            verboseLogs: false
        );

        $converter = new PdfToZplConverter($settings);
        $pages = $converter->convertFromFile($pdf);

        $this->assertIsArray($pages);
        $this->assertCount(3, $pages); // Endicia label has 3 pages

        foreach ($pages as $page) {
            $this->assertIsString($page);
            $this->assertStringContainsString('^XA', $page);
            $this->assertStringContainsString('^XZ', $page);
            $this->assertStringContainsString('^GFA', $page);
        }
    }

    public function testBothProcessorsProduceValidZplOutput(): void {
        $utils = new TestUtils(LoggerFactory::createVoidLogger());
        $duck = $utils->testData("duck.png");

        $gdSettings = new ConverterSettings(
            imageProcessorOption: ImageProcessorOption::Gd,
            verboseLogs: false
        );

        $imagickSettings = new ConverterSettings(
            imageProcessorOption: ImageProcessorOption::Imagick,
            verboseLogs: false
        );

        $gdConverter = new ImageToZplConverter($gdSettings);
        $imagickConverter = new ImageToZplConverter($imagickSettings);

        $gdZpl = $gdConverter->convertFromFile($duck)[0];
        $imagickZpl = $imagickConverter->convertFromFile($duck)[0];

        // Both should start with ^XA
        $this->assertStringStartsWith('^XA', $gdZpl);
        $this->assertStringStartsWith('^XA', $imagickZpl);

        // Both should end with ^XZ
        $this->assertStringEndsWith('^XZ', $gdZpl);
        $this->assertStringEndsWith('^XZ', $imagickZpl);

        // Both should contain ^GFA (graphic field)
        $this->assertStringContainsString('^GFA', $gdZpl);
        $this->assertStringContainsString('^GFA', $imagickZpl);

        // Both should be non-empty strings
        $this->assertNotEmpty($gdZpl);
        $this->assertNotEmpty($imagickZpl);

        // Both outputs should be different implementations but valid
        $this->assertIsString($gdZpl);
        $this->assertIsString($imagickZpl);
    }
}
