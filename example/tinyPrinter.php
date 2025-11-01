#!/usr/bin/php
<?php

require __DIR__ . "/../vendor/autoload.php";

use Faerber\PdfToZpl\ImageToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;

// An Attempt to get the library working for tiny printers
// like the ZD410 Desktop Printer

$size = 170;
$converter = new ImageToZplConverter(
    new ConverterSettings(
        labelWidth: (int)($size * 1.5),
        labelHeight: (int)($size * 0.7),
    )
);

$testData = __DIR__ . "/../test_data";
$testOutput = __DIR__ . "/../test_output";

[$zpl] = $converter->convertFromFile($testData . "/duck.png");

file_put_contents($testOutput . "/tiny-duck.zpl", '$tinyDuck = "' . $zpl . '"');
