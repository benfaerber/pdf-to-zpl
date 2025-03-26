<?php

namespace Faerber\PdfToZpl\Images;

use Exception;
use Faerber\PdfToZpl\ImagickPixelStub;
use Faerber\PdfToZpl\ImagickStub;
use Faerber\PdfToZpl\Settings\ConverterSettings;

class ImagickProcessor implements ImageProcessor {
    private ImagickStub $img;
    private ConverterSettings $settings;

    public function __construct(ImagickStub $img, ConverterSettings $settings) {
        $this->img = $img;
        $this->settings = $settings;
    }

    public function width(): int {
        return $this->img->getImageWidth();
    }

    public function height(): int {
        return $this->img->getImageHeight();
    }

    public function isPixelBlack(int $x, int $y): bool {
        $pixel = $this->img->getImagePixelColor($x, $y);
        $color = $pixel->getColor();
        $avgColor = ($color['r'] + $color['g'] + $color['b']) / 3;

        return $avgColor < 0.5;
    }

    public function readBlob(string $data): static {
        $blob = $this->img->readImageBlob($data);
        if (! $blob) {
            throw new Exception("Cannot load!");
        }

        $this->img->setImageColorspace(ImagickStub::constant("COLORSPACE_RGB"));
        $this->img->setImageFormat('png');
        $this->img->thresholdImage(0.5 * ImagickStub::getQuantum());
        return $this;
    }

    /** Perform any necessary scaling on the image */
    public function scaleImage(): static {
        if ($this->width() === $this->settings->labelWidth) {
            return $this;
        }

        if ($this->settings->scale->shouldResize()) {
            $this->img->scaleImage(
                $this->settings->labelWidth,
                $this->settings->labelHeight,
                bestfit: $this->settings->scale->isBestFit()
            );
        }
        return $this;
    }

    /** Perform any necessary rotate for landscape PDFs */
    public function rotateImage(): static {
        if ($this->settings->rotateDegrees) {
            $this->img->rotateImage((new ImagickPixelStub("white"))->inner(), $this->settings->rotateDegrees);
        }
        return $this;
    }

    public function processorType(): ImageProcessorOption {
        return ImageProcessorOption::Imagick;
    }
}
