<?php

namespace Faerber\PdfToZpl\Settings;

enum ImageScale {
    /**
    * Scale the Image to fill all available space
    * (does not respect aspect ratio)
    */
    case Fill;

    /**
    * Scale the Image to fill the most available space while respecting aspect ratio
    */
    case Cover;

    /**
    * Do not scale the image in anyway (could cause the image to not fit on the label)
    */
    case None;

    public function shouldResize(): bool {
        return $this === self::Fill || $this === self::Cover;
    }

    public function isBestFit(): bool {
        return $this === self::Cover;
    }
}
