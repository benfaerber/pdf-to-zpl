<?php

namespace Faerber\PdfToZpl\Settings;

use Faerber\PdfToZpl\Images\{ImageProcessorOption, ImageProcessor};
use Faerber\PdfToZpl\PdfToZplException;
use Imagick;
use Psr\Log\LoggerInterface;
use Stringable;

/** Settings for the PDF to ZPL conversion */
class ConverterSettings {
    public const DEFAULT_LABEL_WIDTH = 812;
    public const DEFAULT_LABEL_HEIGHT = 1218;
    public const DEFAULT_LABEL_DPI = 203;

    /** How the image should be scaled to fit on the label */
    public readonly ImageScale $scale;
    /** Dots Per Inch of the desired Label */
    public readonly int $dpi;

    /** The width in Pixels of your label */
    public readonly int $labelWidth;

    /** The height in Pixels of your label */
    public readonly int $labelHeight;

    /** The format to encode the image with */
    public string $imageFormat;

    /** How many degrees to rotate the label. Used for landscape PDFs */
    public int|null $rotateDegrees;

    /** The Image Processing backend to use (example: imagick or GD) */
    public ImageProcessor $imageProcessor;

    /** Log each step of the process */
    public bool $verboseLogs;
    
    /** The logger to use for `verboseLogs`
    * If using Laravel pass: `logger()` */
    public LoggerInterface $logger;

    public function __construct(
        ImageScale $scale = ImageScale::Cover,
        int $dpi = self::DEFAULT_LABEL_DPI,
        int $labelWidth = self::DEFAULT_LABEL_WIDTH,
        int $labelHeight = self::DEFAULT_LABEL_HEIGHT,
        string $imageFormat = "png",
        ImageProcessorOption $imageProcessorOption = ImageProcessorOption::Gd,
        int|null $rotateDegrees = null,
        bool $verboseLogs = false,
        LoggerInterface|null $logger = null,
    ) {
        $this->scale = $scale;
        $this->dpi = $dpi;
        $this->labelWidth = $labelWidth;
        $this->labelHeight = $labelHeight;
        $this->imageFormat = $imageFormat;
        $this->rotateDegrees = $rotateDegrees;
        $this->verboseLogs = $verboseLogs;
        $this->logger = $logger ?: new EchoLogger();
        $this->verifyDependencies($imageProcessorOption);

        $this->imageProcessor = $imageProcessorOption->processor($this);
    }

    private function verifyDependencies(ImageProcessorOption $option): void {
        if (! extension_loaded('gd') && $option === ImageProcessorOption::Gd) {
            throw new PdfToZplException("You must install the GD image library or change imageProcessorOption to ImageProcessOption::Imagick");
        }

        if (! extension_loaded('imagick')) {
            throw new PdfToZplException("You must install the Imagick image library");
        }

        $formats = Imagick::queryFormats();
        if (! array_search("PDF", $formats)) {
            throw new PdfToZplException("Format PDF not allowed for Imagick (try installing ghostscript: sudo apt-get install -y ghostscript)");
        }
    }

    public static function default(): self {
        return new self();
    }

    public function log(mixed ...$messages): void {
        if (! $this->verboseLogs) {
            return;
        }
        foreach ($messages as $message) {
            $message = is_string($message) || $message instanceof Stringable
                ? (string)$message
                : json_encode($message); 
            $this->logger->debug("[pdf-to-zpl] {$message}");
        }
    }
}
