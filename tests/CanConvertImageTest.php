<?php

declare(strict_types=1);

use Faerber\PdfToZpl\ImageToZplConverter;
use Faerber\PdfToZpl\Logger\ColoredLogger;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use PHPUnit\Framework\TestCase;

final class CanConvertImageTest extends TestCase {
    public function testCanConvertDuck(): void {
        $utils = new TestUtils(new ColoredLogger);
        $converter = new ImageToZplConverter(new ConverterSettings(verboseLogs: true, logger: $utils->logger));
        $pages = $converter->convertFromFile($utils->testData("duck.png"));
        $expectedPageCount = 1;

        // Should have 3 pages
        $this->assertEquals(
            count($pages),
            $expectedPageCount,
        );

        // Should match the previously generated data
        $this->assertEquals(
            $pages,
            $utils->loadExpectedPages("expected_duck", count($pages)),
        );
    }
}
