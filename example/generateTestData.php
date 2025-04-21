#!/usr/bin/php
<?php

require __DIR__ . "/../vendor/autoload.php";

use Faerber\PdfToZpl\LabelImage;
use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\Settings\ImageScale;
use Faerber\PdfToZpl\Settings\EchoLogger;
use Faerber\PdfToZpl\ImageToZplConverter;

// Generate Data the unit tests can compare against
// After you've generated the data, view the images in the test_output folder
// and ensure they are correct.
//
// The only reason you would need to regenerate test data is if you've made a
// change that will change the ZPL structure (ie use a different image library or modify scaling code)

$logger = new EchoLogger();
$testData = __DIR__ . "/../test_data";
$testOutput = __DIR__ . "/../test_output";

$settings = new ConverterSettings(
    scale: ImageScale::Cover,
    logger: $logger,
);
$pdfConverter = new PdfToZplConverter($settings);
$imageConverter = new ImageToZplConverter($settings);

$landscapePdfConverter = new PdfToZplConverter(new ConverterSettings(
    rotateDegrees: 90,
));

/** @param string[] $pages */
function downloadPages(array $pages, string $name): void {
    global $testOutput, $logger;
    foreach ($pages as $index => $page) {
        assert(str_starts_with($page, "^XA^GFA,"));

        $basePath = $testOutput . "/{$name}_{$index}";
        $zplFilepath = $basePath . ".zpl.txt";
        if (file_exists($zplFilepath)) {
            continue;
        }

        file_put_contents($zplFilepath, $page);

        $logger->info("Downloading {$name} {$index}");

        $image = new LabelImage(zpl: $page);
        $image->saveAs($basePath . ".png");

        // So we don't get rate limited
        sleep(1);
    }
}


function convertPdfToPages(string $pdf, string $name, PdfToZplConverter $converter): void {
    global $testData, $testOutput, $logger;
    $logger->info("Converting PDF {$name}");
    $pdfFile = $testData . "/" . $pdf;
    $pages = $converter->convertFromFile($pdfFile);
    downloadPages($pages, $name);
}

function convertImageToPages(string $image, string $name): void {
    global $imageConverter, $testData, $testOutput, $logger;
    $logger->info("Converting Image {$name}");
    $imageFile = $testData . "/" . $image;
    $pages = $imageConverter->convertFromFile($imageFile);
    downloadPages($pages, $name);
}


function convertEndiciaLabel(): void {
    global $pdfConverter;
    convertPdfToPages("endicia-shipping-label.pdf", "expected_label", $pdfConverter);
}

function convertDonkeyPdf(): void {
    global $pdfConverter;
    convertPdfToPages("donkey.pdf", "expected_donkey", $pdfConverter);
}

function convertLandscapePdf(): void {
    global $landscapePdfConverter;
    convertPdfToPages("usps-label-landscape.pdf", "expected_usps_landscape", $landscapePdfConverter);
}

function convertDuckImage(): void {
    convertImageToPages("duck.png", "expected_duck");
}

function purgeOld(): void {
    global $testOutput;
    foreach (scandir($testOutput) as $file) {
        if (str_starts_with($file, ".")) {
            continue;
        }

        unlink($testOutput . "/" . $file);
    }
}

purgeOld();
convertEndiciaLabel();
convertDonkeyPdf();
convertDuckImage();
convertLandscapePdf();
