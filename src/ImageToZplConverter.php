<?php

namespace Faerber\PdfToZpl;

use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\Images\ImageProcessor;
use Faerber\PdfToZpl\Settings\Collection;
use Faerber\PdfToZpl\Exceptions\PdfToZplException;

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

    /**
    * @throws PdfToZplException
    */
    public function convertImageToZpl(ImageProcessor $image): string {
        // Width in bytes
        $width = (int) ceil($image->width() / 8);
        $height = $image->height();
        $bitmap = '';
        $lastRow = null;

        for ($y = 0; $y < $height; $y++) {
            $bytes = $this->buildBytes($image, $y);
            $row = $this->compressBytesToHex($bytes); 
            $bitmap .= $this->buildBitmapAddition($row, $lastRow, $y);
            $lastRow = $row;
        }
    
        return $this->buildFinalZplCommand($bitmap, width: $width, height: $height);
    }

    private function buildFinalZplCommand(string $bitmap, int $width, int $height): string {
        $byteCount = $width * $height;
        $parameters = new Collection([
            self::ENCODE_CMD,
            $byteCount,
            $byteCount,
            $width,
            $bitmap
        ]);

        return self::START_CMD
            . $parameters->implode(",")
            . self::END_CMD;
    }

    /**
    * Convert bytes to hex and compress
    * @param string[] $bytes
    */
    private function compressBytesToHex(array $bytes): string {
        return (new Collection($bytes))
            ->map(fn ($byte) => sprintf('%02X', bindec($byte)))
            ->implode('');
    }

    /**
    * @return string[]
    */
    private function buildBytes(ImageProcessor $image, int $y): array {
        $bits = '';

        // Create a binary string for the row
        for ($x = 0; $x < $image->width(); $x++) {
            $bits .= $image->isPixelBlack($x, $y) ? '1' : '0';
        }

        // Convert bits to bytes
        $bytes = str_split($bits, length: 8);
        $lastByte = array_pop($bytes);
        /** @var string|null $lastByte */
        if ($lastByte === null) {
            throw new PdfToZplException("Failed to get last byte");
        }
        $bytes[] = str_pad($lastByte, length: 8, pad_string: '0');
        return $bytes;
    }

    private function buildBitmapAddition(string $row, string|null $lastRow, int $y): string {
        if ($row === $lastRow) {
            return ":";
        }

        $encoded = preg_replace(['/0+$/', '/F+$/'], [',', '!'], $row);
        if ($encoded === null) {
            throw new PdfToZplException("Failed to encode", context: ["y" => $y]);
        }
        return $this->compressRow($encoded);
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
        $replaced = preg_replace_callback('/(.)(\1{2,})/', fn ($matches) => $this->compressSequence($matches[0]), $row);
        if (! $replaced) {
            throw new PdfToZplException("Failed to compress image row");
        }
        return $replaced;
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
