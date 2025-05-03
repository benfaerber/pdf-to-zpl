<?php

namespace Faerber\PdfToZpl\Images;

use Faerber\PdfToZpl\Settings\ConverterSettings;
use Imagick;

enum ImageProcessorOption {
    /**
    * The faster and better processing option, it needs to be installed
    */
    case Gd;

    /**
    * The slower and worse processing option,
    * it is installed by default and is useful in environments where you cannot install extensions
    */
    case Imagick;

    case Vips;

    public function processor(ConverterSettings $settings): ImageProcessor {
        return match ($this) {
            self::Imagick => new ImagickProcessor(new Imagick(), $settings),
            self::Gd => new GdProcessor($settings),
            self::Vips => new VipsProcessor($settings),
        };
    }
}
