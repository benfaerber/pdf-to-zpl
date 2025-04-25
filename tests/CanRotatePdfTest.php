<?php

declare(strict_types=1);

use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\Settings\EchoLogger;
use PHPUnit\Framework\TestCase;

final class CanRotatePdfTest extends TestCase {
    public function testCanRotateLandscapePdf(): void {
        $utils = new TestUtils(new EchoLogger);
        $converter = new PdfToZplConverter(new ConverterSettings(
            verboseLogs: true,
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
        $this->assertGreaterThan(95, $utils->getPercentSimilar(
            $pages,
            $utils->loadExpectedPages("expected_usps_landscape", count($pages)),
            "can rotate landscape"
        ));
    }
}
