<?php

declare(strict_types=1);

use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\ZplConverterFactory;
use PHPUnit\Framework\TestCase;

final class CanUseFactoryTest extends TestCase {
    public function testCanUseFactoryForImage(): void {
        $duck = TestUtils::testData("duck.png");
        $converter = ZplConverterFactory::converterFromFile($duck, new ConverterSettings(verboseLogs: true));
        $pages = $converter->convertFromFile($duck);
        $expectedPageCount = 1;

        $this->assertEquals(
            count($pages),
            $expectedPageCount,
        );

        // Should match the previously generated data
        $this->assertEquals(
            $pages,
            TestUtils::loadExpectedPages("expected_duck", count($pages)),
        );
    }


    public function testCanUseFactoryForPdf(): void {
        $pdf = TestUtils::testData("endicia-shipping-label.pdf");
        $converter = ZplConverterFactory::converterFromFile($pdf, new ConverterSettings(verboseLogs: true));
        $pages = $converter->convertFromFile($pdf);
        $expectedPageCount = 3;

        // Should have 3 pages
        $this->assertEquals(
            count($pages),
            $expectedPageCount,
        );

        // Should match the previously generated data
        $this->assertEquals(
            $pages,
            TestUtils::loadExpectedPages("expected_label", count($pages)),
        );
    }
}
