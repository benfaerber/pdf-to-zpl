#!/usr/bin/php
<?php

require __DIR__ . "/../vendor/autoload.php";

use Faerber\PdfToZpl\LabelImage;
use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\Settings\ImageScale;
use Faerber\PdfToZpl\Logger\LoggerFactory;
use Faerber\PdfToZpl\ImageToZplConverter;
use Psr\Log\LoggerInterface;

// Generate Data the unit tests can compare against
// After you've generated the data, view the images in the test_output folder
// and ensure they are correct.
//
// The only reason you would need to regenerate test data is if you've made a
// change that will change the ZPL structure (ie use a different image library or modify scaling code)



class GenerateTestData {
    private ConverterSettings $settings;
    private PdfToZplConverter $pdfConverter;
    private ImageToZplConverter $imageConverter;
    private PdfToZplConverter $landscapePdfConverter;
    private LoggerInterface $logger;

    public function __construct(
        ?LoggerInterface $logger = null,
        private string $testData = __DIR__ . "/../test_data",
        private string $testOutput = __DIR__ . "/../test_output",
    ) {
        $this->logger = $logger ?: LoggerFactory::createEchoLogger();
        $this->settings = new ConverterSettings(
            scale: ImageScale::Cover,
            logger: $this->logger,
        );
        $this->pdfConverter = new PdfToZplConverter($this->settings);
        $this->imageConverter = new ImageToZplConverter($this->settings);

        $this->landscapePdfConverter = new PdfToZplConverter(new ConverterSettings(
            rotateDegrees: 90,
        ));
    }

    /** @param string[] $pages */
    function downloadPages(array $pages, string $name): void {
        foreach ($pages as $index => $page) {
            assert(str_starts_with($page, "^XA^GFA,"));

            $basePath = $this->testOutput . "/{$name}_{$index}";
            $zplFilepath = $basePath . ".zpl.txt";
            if (file_exists($zplFilepath)) {
                continue;
            }

            file_put_contents($zplFilepath, $page);

            $this->logger->info("Downloading {$name} {$index}");

            $image = new LabelImage(zpl: $page);
            $image->saveAs($basePath . ".png");

            // So we don't get rate limited
            sleep(1);
        }
    }

    function convertPdfToPages(string $pdf, string $name, PdfToZplConverter $converter): void {
        $this->logger->info("Converting PDF {$name}");
        $pdfFile = $this->testData . "/" . $pdf;
        $pages = $converter->convertFromFile($pdfFile);
        $this->downloadPages($pages, $name);
    }

    function convertImageToPages(string $image, string $name): void {
        $this->logger->info("Converting Image {$name}");
        $imageFile = $this->testData . "/" . $image;
        $pages = $this->imageConverter->convertFromFile($imageFile);
        $this->downloadPages($pages, $name);
    }


    function convertEndiciaLabel(): void {
        $this->convertPdfToPages("endicia-shipping-label.pdf", "expected_label", $this->pdfConverter);
    }

    function convertDonkeyPdf(): void {
        $this->convertPdfToPages("donkey.pdf", "expected_donkey", $this->pdfConverter);
    }

    function convertLandscapePdf(): void {
        $this->convertPdfToPages("usps-label-landscape.pdf", "expected_usps_landscape", $this->landscapePdfConverter);
    }

    function convertDuckImage(): void {
        $this->convertImageToPages("duck.png", "expected_duck");
    }

    function purgeOld(): void {
        $this->logger->info("Deleting old info!"); 
        foreach (scandir($this->testOutput) as $file) {
            if (str_starts_with($file, ".")) {
                continue;
            }

            unlink($this->testOutput . "/" . $file);
        }
    }
    
    public function handle(): void {
        $this->purgeOld();
        $this->convertEndiciaLabel();
        $this->convertDonkeyPdf();
        $this->convertDuckImage();
        $this->convertLandscapePdf();
        exit(0);
    }
}

(new GenerateTestData())->handle();
