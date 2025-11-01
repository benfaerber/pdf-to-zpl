<?php

declare(strict_types=1);

use Faerber\PdfToZpl\Logger\LoggerFactory;
use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use PHPUnit\Framework\TestCase;

final class CanRotatePdfTest extends TestCase {
    public function testCanRotateLandscapePdf(): void {
        $utils = new TestUtils(LoggerFactory::createColoredLogger());
        $converter = new PdfToZplConverter(new ConverterSettings(
            verboseLogs: true,
            logger: $utils->logger,
            rotateDegrees: 90,
        ));
        $pages = $converter->convertFromFile($utils->testData("usps-label-landscape.pdf"));
        $expectedPageCount = 4;

        // Should have 3 pages
        $this->assertEquals(
            count($pages),
            $expectedPageCount,
        );

        // Should match the previously generated data
        $this->assertGreaterThan(95, $utils->percentSimilarToExpected(
            $pages,
            "expected_usps_landscape",
            "can rotate landscape"
        ));
    }
}
