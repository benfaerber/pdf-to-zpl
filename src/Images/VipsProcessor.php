<?php

namespace Faerber\PdfToZpl\Images;

use Exception;
use Faerber\PdfToZpl\PdfToZplException;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Jcupitt\Vips;
use Jcupitt\Vips\Image as VipsImage;

class VipsProcessor implements ImageProcessor {
    private VipsImage $img; 
    
    public function __construct(private ConverterSettings $settings) {
    }
    
    public function width(): int {
        return $this->img->width;
    }

    public function height(): int {
        return $this->img->height;
    }

    public function isPixelBlack(int $x, int $y): bool {
        $point = $this->img->getpoint($x, $y);

        $total = 0;
        foreach ($point as $entry) {
            $total += $entry;
        }
        $avg = $total / count($point);

        file_put_contents("./test_output/point", json_encode($avg));
        $this->settings->log("Point:", $avg);
        return $avg > 128;
    }

    public function readBlob(string $data): static {
        $this->settings->log("VIPS Reading from buffer");
        $this->settings->log("Data Size: " . strlen($data));
        $this->img = VipsImage::newFromBuffer($data, '', ['access' => 'sequential']);
        return $this;
    }
    
    public function scaleImage(): static {
        return $this;
    }
    
    public function rotateImage(): static {
        return $this;
    }
    
    public function processorType(): ImageProcessorOption {
        return ImageProcessorOption::Vips;
    }
}
