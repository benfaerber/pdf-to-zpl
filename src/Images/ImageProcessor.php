<?php

namespace Faerber\PdfToZpl\Images;

/** An image backend able to process the convertion to ZPL */
interface ImageProcessor {
    /** The width of the image in pixels */
    public function width(): int;
    /** The height of the image in pixels */
    public function height(): int;
    /**
    * Should this pixel be rendered as black when the image is monochromed?
    * Usually, this should be checked if the rgb average is greater than 128
    */
    public function isPixelBlack(int $x, int $y): bool;

    /**
    * Read an image from binary data stored in a string
    */
    public function readBlob(string $data): static;
    /**
    * Apply the proper scaling mentioned in {@see ConverterSettings}
    */
    public function scaleImage(): static;

    /**
    * Rotate the image as requested in {@see ConverterSettings}
    */
    public function rotateImage(): static;
    /** What backend does this processor use? */
    public function processorType(): ImageProcessorOption;
}
