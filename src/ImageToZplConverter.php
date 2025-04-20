<?php

namespace Faerber\PdfToZpl;

use Exception;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\Images\ImageProcessor;
use Illuminate\Support\Collection;

/**
 * Convert an Image to Zpl
 *
 * @see https://github.com/himansuahm/php-zpl-converter
 */
class ImageToZplConverter implements ZplConverterService {
    public ConverterSettings $settings;

    public function __construct(
        ConverterSettings|null $settings = null,
    ) {
        $this->settings = $settings ?? new ConverterSettings();
    }

    public const START_CMD = "^XA";
    public const END_CMD = "^XZ";
    private const ENCODE_CMD = "^GFA";

    public function convertImageToZpl(ImageProcessor $image): string {
        // Width in bytes
        $width = (int) ceil($image->width() / 8);
        $height = $image->height();
        $bitmap = '';
        $lastRow = null;

        for ($y = 0; $y < $height; $y++) {
            $bits = '';

            // Create a binary string for the row
            for ($x = 0; $x < $image->width(); $x++) {
                $bits .= $image->isPixelBlack($x, $y) ? '1' : '0';
            }

            // Convert bits to bytes
            $bytes = str_split($bits, length: 8);
            $bytes[] = str_pad(array_pop($bytes), length: 8, pad_string: '0');

            // Convert bytes to hex and compress
            $row = (new Collection($bytes))
                ->map(fn ($byte) => sprintf('%02X', bindec($byte)))
                ->implode('');

            $bitmap .= ($row === $lastRow) ? ':' : $this->compressRow(preg_replace(['/0+$/', '/F+$/'], [',', '!'], $row));
            $lastRow = $row;
        }

        // Prepare ZPL command parameters
        $byteCount = $width * $height;
        $parameters = new Collection([
            self::ENCODE_CMD,
            $byteCount,
            $byteCount,
            $width,
            $bitmap
        ]);

        return (new Collection([
            self::START_CMD,
            $parameters->implode(","),
            self::END_CMD,
        ]))->implode('');
    }

    /**
     * @param string $rawImage The binary data of an image saved as a string (can be GIF, PNG or JPEG)
     */
    private function loadFromRawImage(string $rawImage, ImageProcessor $processor): ImageProcessor {
        return $processor->readBlob($rawImage);
    }

    /** This can just be a string (the first few bytes say if its a GIF or PNG or whatever) */
    public function rawImageToZpl(string $rawImage): string {
        $img = $this->loadFromRawImage($rawImage, $this->settings->imageProcessor);
        $img->scaleImage();
        return $this->convertImageToZpl($img);
    }

    public function convertFromBlob(string $rawData): array {
        return [$this->rawImageToZpl($rawData)];
    }

    public function convertFromFile(string $filepath): array {
        $rawData = @file_get_contents($filepath);
        if (! $rawData) {
            throw new PdfToZplException("Invalid file {$filepath}");
        }
        return $this->convertFromBlob($rawData);
    }

    public static function canConvert(): array {
        return ["png", "gif"];
    }

    /** Run Line Encoder (replace repeating characters) */
    private function compressRow(string $row): string {
        return preg_replace_callback('/(.)(\1{2,})/', fn ($matches) => $this->compressSequence($matches[0]), $row);
    }

    private function compressSequence(string $sequence): string {
        $repeat = strlen($sequence);
        $count = '';

        if ($repeat > 400) {
            $count .= str_repeat('z', (int)floor($repeat / 400));
            $repeat %= 400;
        }

        if ($repeat > 19) {
            $count .= chr(ord('f') + (int)floor($repeat / 20));
            $repeat %= 20;
        }

        if ($repeat > 0) {
            $count .= chr(ord('F') + $repeat);
        }

        return $count . substr($sequence, 1, 1);
    }
}
