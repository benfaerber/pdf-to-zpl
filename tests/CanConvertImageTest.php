<?php

declare(strict_types=1);

use Faerber\PdfToZpl\ImageToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use PHPUnit\Framework\TestCase;

final class CanConvertImageTest extends TestCase
{
    public function testCanConvertDuck()
    {
        $converter = new ImageToZplConverter(new ConverterSettings(verboseLogs: true));
        $pages = $converter->convertFromFile(TestUtils::testData("duck.png"));
        $expectedPageCount = 1;

        // Should have 3 pages
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
}
