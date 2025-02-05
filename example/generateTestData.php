<?php

require __DIR__ . "/../vendor/autoload.php";

use Faerber\PdfToZpl\LabelImage;
use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\Settings\ImageScale;
use Faerber\PdfToZpl\ImageToZplConverter;

// Generate Data the unit tests can compare against
// After you've generated the data, view the images in the test_output folder
// and ensure they are correct.
//
// The only reason you would need to regenerate test data is if you've made a
// change that will change the ZPL structure (ie use a different image library or modify scaling code)

$testData = __DIR__ . "/../test_data";
$testOutput = __DIR__ . "/../test_output";

$settings = new ConverterSettings(
    scale: ImageScale::Cover,
);
$pdfConverter = new PdfToZplConverter($settings);
$imageConverter = new ImageToZplConverter($settings);

$landscapePdfConverter = new PdfToZplConverter(new ConverterSettings(
    rotateDegrees: 90,
));

function downloadPages(array $pages, string $name)
{
    global $testOutput;
    foreach ($pages as $index => $page) {
        assert(str_starts_with($page, "^XA^GFA,"));

        $basePath = $testOutput . "/{$name}_{$index}";
        $zplFilepath = $basePath . ".zpl.txt";
        if (file_exists($zplFilepath)) {
            continue;
        }

        file_put_contents($zplFilepath, $page);

        echo "Downloading {$name} {$index}\n";

        $image = new LabelImage(zpl: $page);
        $image->saveAs($basePath . ".png");

        // So we don't get rate limited
        sleep(1);
    }
}


function convertPdfToPages(string $pdf, string $name, PdfToZplConverter $converter)
{
    echo "Converting PDF {$name}\n";
    global $testData, $testOutput;
    $pdfFile = $testData . "/" . $pdf;
    $pages = $converter->convertFromFile($pdfFile);
    downloadPages($pages, $name);
}

function convertImageToPages(string $image, string $name)
{
    echo "Converting Image {$name}\n";
    global $imageConverter, $testData, $testOutput;
    $imageFile = $testData . "/" . $image;
    $pages = $imageConverter->convertFromFile($imageFile);
    downloadPages($pages, $name);
}


function convertEndiciaLabel()
{
    global $pdfConverter;
    convertPdfToPages("endicia-shipping-label.pdf", "expected_label", $pdfConverter);
}

function convertDonkeyPdf()
{
    global $pdfConverter;
    convertPdfToPages("donkey.pdf", "expected_donkey", $pdfConverter);
}

function convertLandscapePdf()
{
    global $landscapePdfConverter;
    convertPdfToPages("usps-label-landscape.pdf", "expected_usps_landscape", $landscapePdfConverter);
}

function convertDuckImage()
{
    convertImageToPages("duck.png", "expected_duck");
}

function purgeOld()
{
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
