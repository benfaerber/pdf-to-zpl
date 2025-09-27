<?php

namespace Faerber\PdfToZpl;

use Faerber\PdfToZpl\Images\ImagickProcessor;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\Settings\Collection;
use Faerber\PdfToZpl\Exceptions\PdfToZplException;
use Faerber\PdfToZpl\Images\ImageProcessor;
use Imagick;
use ImagickException;
use ImagickPixel;

/** Converts a PDF file into a list of ZPL commands */
class PdfToZplConverter implements ZplConverterService {
    public ConverterSettings $settings;
    private ImageToZplConverter $imageConverter;
    
    /** The error code for PDF permission issue */  
    private const IMAGICK_SECURITY_CODE = 499;

    public function __construct(
        ConverterSettings|null $settings = null,
    ) {
        $this->settings = $settings ?? new ConverterSettings();
        $this->imageConverter = new ImageToZplConverter($this->settings);
    }

    public static function build(ConverterSettings $settings): static {
        return new self($settings);
    }
    
    // Normal sized PDF: A4, Portrait (8.27 × 11.69 inch)
    // Desired sized PDF: prc 32k, Portrait (3.86 × 6.00 inch)

    /**
    * @return Collection<int, string>
    */
    private function pdfToZpls(string $pdfData): Collection {
        return $this->pdfToImages($pdfData)
            ->map(fn ($img) => $this->imageConverter->rawImageToZpl($img));
    }

    /** Add a white background to the label */
    private function background(Imagick $img): Imagick {
        $background = new Imagick();
        $pixel = new ImagickPixel('white');
        $background->newImage($img->getImageWidth(), $img->getImageHeight(), $pixel);

        $background->setImageFormat(
            $img->getImageFormat()
        );

        $background->compositeImage($img, Imagick::COMPOSITE_OVER, 0, 0);

        return $background;
    }

    /**
    * @param string $pdfData Raw PDF data as a string
    * @return Collection<int, string> A list of raw PNG data as a string
    * @throws PdfToZplException
    */
    private function pdfToImages(string $pdfData): Collection {
        $img = new Imagick();
        $dpi = $this->settings->dpi;
        $img->setResolution($dpi, $dpi);
        $this->attemptReadBlob($img, $pdfData);

        $pages = $img->getNumberImages();
        $this->settings->log("Page count = " . $pages);
        $processor = new ImagickProcessor($img, $this->settings);
        $images = new Collection([]);
        for ($i = 0; $i < $pages; $i++) {
            $this->settings->log("Working on page " . $i);
            $page = $this->processPage($img, $processor, pageIndex: $i);
            $images->push((string)$page);
        }
        $img->clear();

        return $images;
    }


    private function processPage(Imagick $img, ImageProcessor $imageProcessor, int $pageIndex): Imagick {
        $img->setIteratorIndex($pageIndex);

        $img->setImageCompressionQuality(100);

        $imageProcessor
            ->scaleImage()
            ->rotateImage();

        $img->setImageFormat('png');
        return $this->background($img);
    }

    private function attemptReadBlob(Imagick $img, string $pdfData): void {
        try {
            $img->readImageBlob($pdfData);
            $this->settings->log("Read blob...");
        } catch (ImagickException $exception) {
            if ($exception->getCode() === self::IMAGICK_SECURITY_CODE) {
                throw new PdfToZplException(
                    "You need to enable PDF reading and writing in your Imagick settings (see docs for more details)", 
                    code: self::IMAGICK_SECURITY_CODE, 
                    previous: $exception,
                );
            }
            // No special handling
            throw $exception;
        }
    }

    /**
    * Convert raw PDF data into an array of ZPL commands.
    * Each page of the PDF is 1 ZPL command.
    *
    * @return string[]
    */
    public function convertFromBlob(string $pdfData): array {
        return $this->pdfToZpls($pdfData)->toArray();
    }

    /**
    * Load a PDF file and convert it into an array of ZPL commands.
    * Each page of the PDF is 1 ZPL command.
    * 
    * @throws PdfToZplException
    */
    public function convertFromFile(string $filepath): array {
        $rawData = @file_get_contents($filepath);
        if (! $rawData) {
            throw new PdfToZplException("File {$filepath} does not exist!");
        }
        $this->settings->log("File Size for {$filepath} is " . strlen($rawData));

        return $this->convertFromBlob($rawData);
    }

    /** Extensions this converter is able to process */
    public static function canConvert(): array {
        return ["pdf"];
    }
}
