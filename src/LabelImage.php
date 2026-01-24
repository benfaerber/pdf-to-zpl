<?php

declare(strict_types=1);

namespace Faerber\PdfToZpl;

use GuzzleHttp\Client as GuzzleClient;
use Faerber\PdfToZpl\Settings\LabelDirection;
use Faerber\PdfToZpl\Exceptions\PdfToZplException;

/**
 * A binary PNG image of a ZPL label fetched from `labelary.com`
 * This is a great way to debug or give users a preview before printing
 */
class LabelImage {
    public const URL = "https://api.labelary.com/v1/printers/8dpmm/labels";
    public string $image;

    private GuzzleClient $client;
    private static GuzzleClient|null $globalClient = null;
    private static ImageToZplConverter|null $imageConverter = null;

    public function __construct(
        public string $zpl,
        public LabelDirection $direction = LabelDirection::Up,
        public float $width = 4,
        public float $height = 6,
        GuzzleClient|null $client = null,
    ) {
        if ($client !== null) {
            $this->client = $client;
        } else {
            self::$globalClient ??= new GuzzleClient();
            $this->client = self::$globalClient;
        }

        $this->download();
    }

    /** Download and return a raw PNG as a string */
    public function download(): string {
        $headers = [
            'Accept' => 'image/png',
            'X-Rotation' => strval($this->direction->toDegree()),
        ];

        $url = self::URL . "/{$this->width}x{$this->height}/0/";
        
        $response = $this->client->post($url, [
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

        $this->image = (string)$response->getBody();
        return $this->image;
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
