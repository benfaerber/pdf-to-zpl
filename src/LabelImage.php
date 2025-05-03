<?php

namespace Faerber\PdfToZpl;

use GuzzleHttp\Client as GuzzleClient;
use Faerber\PdfToZpl\Settings\LabelDirection;
use Faerber\PdfToZpl\Exceptions\PdfToZplException;

/**
 * A binary PNG image of a ZPL label fetched from `labelary.com`
 * This is a great way to debug or give users a preview before printing
 *
 * Only 5 requests are allowed per second!
 */
class LabelImage {
    public const URL = "http://api.labelary.com/v1/printers/8dpmm/labels";
    public string $image;

    private static GuzzleClient $httpClient;
    private static ImageToZplConverter|null $imageConverter = null;

    public function __construct(
        public string $zpl,
        public LabelDirection $direction = LabelDirection::Up,
        public float $width = 4,
        public float $height = 6,
    ) {
        self::$httpClient ??= new GuzzleClient();
        $this->image = $this->download();
    }

    /** Download and return a raw PNG as a string */
    public function download(): string {
        $headers = [
            'Accept' => 'image/png',
            'X-Rotation' => strval($this->direction->toDegree()),
        ];

        $url = self::URL . "/{$this->width}x{$this->height}/0/";
        
        $response = self::$httpClient->post($url, [
            'headers' => $headers,
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => $this->zpl,
                ]
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new PdfToZplException("Failed to Download Image!");
        }

        return (string)$response->getBody();
    }

    /**
    * For use in HTML image tags. `<img src="{{ $label->asHtmlImage() }}" />`
    */
    public function asHtmlImage(): string {
        return "data:image/png;base64," . base64_encode($this->image);
    }

    /** A raw binary data of the image. Can be saved to disk or uploaded */
    public function asRaw(): string {
        return $this->image;
    }

    /**
    * Use the binary form of this image in a ZPL statement
    * This bypasses the printer's font encoder allowing any
    * character / font
    */
    public function toZpl(): string {
        self::$imageConverter ??= new ImageToZplConverter();
        return self::$imageConverter->rawImageToZpl($this->asRaw());
    }

    /** Save the image to disk */
    public function saveAs(string $filepath): void {
        file_put_contents($filepath, $this->asRaw());
    }
}
