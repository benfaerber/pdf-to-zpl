<?php

declare(strict_types=1);

use Faerber\PdfToZpl\Images\ImageProcessorOption;
use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\Settings\EchoLogger;
use PHPUnit\Framework\TestCase;

final class CanConvertPdfTest extends TestCase {
    public function testCanConvertEndiciaPdf(): void {
        $utils = new TestUtils(new EchoLogger);
        $converter = new PdfToZplConverter(new ConverterSettings(verboseLogs: true));
        $pages = $converter->convertFromFile($utils->testData("endicia-shipping-label.pdf"));
        $expectedPageCount = 3;

        // Should have 3 pages
        $this->assertEquals(
            count($pages),
            $expectedPageCount,
        );

        // Should match the previously generated data
        $this->assertTrue($utils->isZplSimilar(
            $pages,
            $utils->loadExpectedPages("expected_label", count($pages)),
            "can convert endicia pdf"
        ));
    }

    public function testCanConvertDonkeyPdf(): void {
        $utils = new TestUtils(new EchoLogger);
        $converter = new PdfToZplConverter(new ConverterSettings(verboseLogs: true));
        $pages = $converter->convertFromFile($utils->testData("donkey.pdf"));
        $expectedPageCount = 9;

        // Should have 9 pages
        $this->assertEquals(
            count($pages),
            $expectedPageCount,
        );

        // Should match the previously generated data
        $this->assertTrue($utils->isZplSimilar(
            $pages,
            $utils->loadExpectedPages("expected_donkey", count($pages)),
            "can convert donkey",
        ));
    }
}
