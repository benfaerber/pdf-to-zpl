<?php

declare(strict_types=1);

use Faerber\PdfToZpl\LabelImage;
use Faerber\PdfToZpl\Settings\LabelDirection;
use PHPUnit\Framework\TestCase;

final class LabelImageTest extends TestCase {
    private string $simpleZpl = "^XA^FO50,50^ADN,36,20^FDTest Label^FS^XZ";
    private static ?LabelImage $cachedLabel = null;

    /**
     * Get a cached LabelImage to avoid hitting API rate limits
     * The Labelary API only allows 5 requests per second
     */
    private function getCachedLabel(): LabelImage {
        if (self::$cachedLabel === null) {
            self::$cachedLabel = new LabelImage($this->simpleZpl);
            // Small delay to avoid rate limiting
            usleep(250000); // 250ms
        }
        return self::$cachedLabel;
    }

    public function testCanCreateLabelImageAndHitsApi(): void {
        if ($this->shouldSkipTest()) {
            $this->markTestSkipped('Skipping API test - only runs on PHP 8.4 to avoid rate limiting');
        }

        $label = $this->getCachedLabel();

        // Should have image data from API
        $this->assertNotEmpty($label->image);
        $this->assertGreaterThan(100, strlen($label->image), "Image should have substantial data");
    }

    public function testDownloadsValidPngImage(): void {
        if ($this->shouldSkipTest()) {
            $this->markTestSkipped('Skipping API test - only runs on PHP 8.4 to avoid rate limiting');
        }

        $label = $this->getCachedLabel();
        $image = $label->asRaw();

        // Check PNG magic bytes (first 8 bytes should be PNG signature)
        $pngSignature = "\x89PNG\r\n\x1a\n";
        $this->assertEquals(
            $pngSignature,
            substr($image, 0, 8),
            "Image should be a valid PNG"
        );
    }

    public function testAsHtmlImageReturnsDataUri(): void {
        if ($this->shouldSkipTest()) {
            $this->markTestSkipped('Skipping API test - only runs on PHP 8.4 to avoid rate limiting');
        }

        $label = $this->getCachedLabel();
        $html = $label->asHtmlImage();

        // Should start with data URI prefix
        $this->assertStringStartsWith('data:image/png;base64,', $html);

        // Should be valid base64 after the prefix
        $base64Part = substr($html, strlen('data:image/png;base64,'));
        $this->assertNotEmpty($base64Part);
        $this->assertNotFalse(base64_decode($base64Part, true));
    }

    public function testAsRawReturnsImageData(): void {
        if ($this->shouldSkipTest()) {
            $this->markTestSkipped('Skipping API test - only runs on PHP 8.4 to avoid rate limiting');
        }

        $label = $this->getCachedLabel();
        $raw = $label->asRaw();

        $this->assertNotEmpty($raw);
        $this->assertEquals($label->image, $raw);
    }

    public function testCanSaveImageToFile(): void {
        if ($this->shouldSkipTest()) {
            $this->markTestSkipped('Skipping API test - only runs on PHP 8.4 to avoid rate limiting');
        }

        $label = $this->getCachedLabel();
        $tempFile = sys_get_temp_dir() . '/test_label_' . uniqid() . '.png';

        try {
            $label->saveAs($tempFile);

            // File should exist
            $this->assertFileExists($tempFile);

            // File contents should match image data
            $this->assertEquals($label->image, file_get_contents($tempFile));

            // Should be a valid PNG
            $imageInfo = getimagesize($tempFile);
            $this->assertNotFalse($imageInfo);
            $this->assertEquals('image/png', $imageInfo['mime']);
        } finally {
            // Clean up
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testCustomDimensions(): void {
        if ($this->shouldSkipTest()) {
            $this->markTestSkipped('Skipping API test - only runs on PHP 8.4 to avoid rate limiting');
        }

        // Sleep to avoid rate limiting
        usleep(250000); // 250ms

        $label = new LabelImage(
            zpl: $this->simpleZpl,
            width: 2,
            height: 3
        );

        $this->assertNotEmpty($label->image);

        // Image should be a valid PNG
        $pngSignature = "\x89PNG\r\n\x1a\n";
        $this->assertEquals($pngSignature, substr($label->image, 0, 8));
    }

    public function testCustomDirection(): void {
        if ($this->shouldSkipTest()) {
            $this->markTestSkipped('Skipping API test - only runs on PHP 8.4 to avoid rate limiting');
        }

        // Sleep to avoid rate limiting
        usleep(250000); // 250ms

        $label = new LabelImage(
            zpl: $this->simpleZpl,
            direction: LabelDirection::Right
        );

        $this->assertNotEmpty($label->image);

        // Image should be a valid PNG
        $pngSignature = "\x89PNG\r\n\x1a\n";
        $this->assertEquals($pngSignature, substr($label->image, 0, 8));
    }

    /**
     * To avoid getting rate limited, only run this test on 1 version (I run 4 versions in Github actions)
     */
    private function shouldSkipTest(): bool {
        return version_compare(PHP_VERSION, '8.4', '<') || version_compare(PHP_VERSION, '8.5', '>=');
    }
}
