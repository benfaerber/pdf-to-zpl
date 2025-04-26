#!/usr/bin/php
<?php

require __DIR__ . "/../vendor/autoload.php";

use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\Settings\ImageScale;
use Faerber\PdfToZpl\Settings\EchoLogger;

$logger = new EchoLogger();
$testData = __DIR__ . "/../test_data";
$testOutput = __DIR__ . "/../test_output";

$settings = new ConverterSettings(
    scale: ImageScale::Cover,
);
$converter = new PdfToZplConverter($settings);
$endiciaShippingLabel = $testData . "/endicia-shipping-label.pdf";
$pages = $converter->convertFromFile($endiciaShippingLabel);

foreach ($pages as $page) {
    assert(str_starts_with($page, "^XA^GFA,"));
    $logger->info($page . "\n\n\n");
}
