<?php

declare(strict_types=1);

use Faerber\PdfToZpl\Images\ImageProcessorOption;
use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use PHPUnit\Framework\TestCase;

final class CanConvertPdfTest extends TestCase {
    public function testCanConvertEndiciaPdf(): void {
        $converter = new PdfToZplConverter(new ConverterSettings(verboseLogs: true));
        $pages = $converter->convertFromFile(TestUtils::testData("endicia-shipping-label.pdf"));
        $expectedPageCount = 3;

        // Should have 3 pages
        $this->assertEquals(
            count($pages),
            $expectedPageCount,
        );

        // Should match the previously generated data
        $this->assertTrue(TestUtils::isZplSimilar(
            $pages,
            TestUtils::loadExpectedPages("expected_label", count($pages)),
        ));
    }

    public function testCanConvertDonkeyPdf(): void {
        $converter = new PdfToZplConverter(new ConverterSettings(verboseLogs: true));
        $pages = $converter->convertFromFile(TestUtils::testData("donkey.pdf"));
        $expectedPageCount = 9;

        // Should have 9 pages
        $this->assertEquals(
            count($pages),
            $expectedPageCount,
        );

        // Should match the previously generated data
        $this->assertTrue(TestUtils::isZplSimilar(
            $pages,
            TestUtils::loadExpectedPages("expected_donkey", count($pages)),
        ));
    }
}
