<?php

namespace Faerber\PdfToZpl\Images;

use Faerber\PdfToZpl\PdfToZplException;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use GdImage;

class GdProcessor implements ImageProcessor {
    private GdImage $img;
    private ConverterSettings $settings;

    public function __construct(ConverterSettings $settings) {
        $this->settings = $settings;
    }

    public function width(): int {
        return imagesx($this->img);
    }

    public function height(): int {
        return imagesy($this->img);
    }

    public function isPixelBlack(int $x, int $y): bool {
        return (imagecolorat($this->img, $x, $y) & 0xFF) < 127;
    }

    public function readBlob(string $data): static {
        $this->img = imagecreatefromstring($data);
        if (! $this->img) {
            throw new PdfToZplException("Failure!");
        }

        imagepalettetotruecolor($this->img);
        return $this;
    }

    public function scaleImage(): static {
        if (!$this->settings->scale->shouldResize() || $this->width() === $this->settings->labelWidth) {
            return $this;
        }

        $srcWidth = imagesx($this->img);
        $srcHeight = imagesy($this->img);

        $dstWidth = $this->settings->labelWidth;
        $dstHeight = $this->settings->labelHeight;

        if ($this->settings->scale->isBestFit()) {
            $aspectRatio = $srcWidth / $srcHeight;
            if ($srcWidth > $srcHeight) {
                $dstHeight = (int) ($dstWidth / $aspectRatio);
            } else {
                $dstWidth = (int) ($dstHeight * $aspectRatio);
            }
        }

        $scaledImg = imagecreatetruecolor($dstWidth, $dstHeight);

        imagecopyresampled(
            $scaledImg,
            $this->img,
            dst_x: 0,
            dst_y: 0,
            src_x: 0,
            src_y: 0,
            dst_width: $dstWidth,
            dst_height: $dstHeight,
            src_width: $srcWidth,
            src_height: $srcHeight
        );

        $this->img = $scaledImg;

        return $this;
    }

    public function rotateImage(): static {
        if ($this->settings->rotateDegrees) {
            $this->img = imagerotate($this->img, $this->settings->rotateDegrees, 0);
        }
        return $this;
    }

    public function processorType(): ImageProcessorOption {
        return ImageProcessorOption::Gd;
    }
}
