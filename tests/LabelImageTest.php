<?php

declare(strict_types=1);

use Faerber\PdfToZpl\LabelImage;
use Faerber\PdfToZpl\Settings\LabelDirection;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

/**
 * @group api
 */
final class LabelImageTest extends TestCase {
    private string $simpleZpl = "^XA^FO50,50^ADN,36,20^FDTest Label^FS^XZ";
    private string $fakePng;

    protected function setUp(): void {
        parent::setUp();
        // A valid 1x1 PNG image
        $this->fakePng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
    }

    private function getMockLabel(string|null $zpl = null, LabelDirection $direction = LabelDirection::Up, float $width = 4, float $height = 6): LabelImage {
        $mock = new MockHandler([
            new Response(200, [], $this->fakePng),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        return new LabelImage(
            zpl: $zpl ?? $this->simpleZpl,
            direction: $direction,
            width: $width,
            height: $height,
            client: $client
        );
    }

    public function testCanCreateLabelImageAndHitsApi(): void {
        $label = $this->getMockLabel();

        // Should have image data from API
        $this->assertNotEmpty($label->image);
        $this->assertEquals($this->fakePng, $label->image);
    }

    public function testDownloadsValidPngImage(): void {
        $label = $this->getMockLabel();
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
        $label = $this->getMockLabel();
        $html = $label->asHtmlImage();

        // Should start with data URI prefix
        $this->assertStringStartsWith('data:image/png;base64,', $html);

        // Should be valid base64 after the prefix
        $base64Part = substr($html, strlen('data:image/png;base64,'));
        $this->assertNotEmpty($base64Part);
        $this->assertNotFalse(base64_decode($base64Part, true));
    }

    public function testAsRawReturnsImageData(): void {
        $label = $this->getMockLabel();
        $raw = $label->asRaw();

        $this->assertNotEmpty($raw);
        $this->assertEquals($label->image, $raw);
    }

    public function testCanSaveImageToFile(): void {
        $label = $this->getMockLabel();
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
        $label = $this->getMockLabel(width: 2, height: 3);

        $this->assertNotEmpty($label->image);

        // Image should be a valid PNG
        $pngSignature = "\x89PNG\r\n\x1a\n";
        $this->assertEquals($pngSignature, substr($label->image, 0, 8));
    }

    public function testCustomDirection(): void {
        $label = $this->getMockLabel(direction: LabelDirection::Right);

        $this->assertNotEmpty($label->image);

        // Image should be a valid PNG
        $pngSignature = "\x89PNG\r\n\x1a\n";
        $this->assertEquals($pngSignature, substr($label->image, 0, 8));
    }
}
