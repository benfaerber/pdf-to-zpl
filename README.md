<h1>
PDF to ZPL
<a href="https://packagist.org/packages/faerber/pdf-to-zpl"><img src="https://img.shields.io/packagist/v/faerber/pdf-to-zpl" /></a>
<a href="https://github.com/benfaerber/pdf-to-zpl/actions"><img src="https://github.com/benfaerber/pdf-to-zpl/actions/workflows/php-ubuntu.yml/badge.svg" /></a>
<a href="https://github.com/benfaerber/pdf-to-zpl/actions"><img src="https://github.com/benfaerber/pdf-to-zpl/actions/workflows/php-windows.yml/badge.svg" /></a>
</h1>

<p>
<a href="phpstan.neon"><img src="https://img.shields.io/badge/PHPStan-level%2010-brightgreen?logo=php" /></a>
<a href="LICENSE"><img src="https://img.shields.io/github/license/benfaerber/pdf-to-zpl?color=yellowgreen" /></a>
</p>


<img src="./static/donkey-label.jpg" alt="A label created with pdf-to-zpl" />

Convert a PDF into the ZPL format. Allowing for custom images, alphabets like Hebrew, Arabic and Cyrillic (without messing with fonts on the printer) and multipage shipping labels!

## Gettings Started:

```
composer require faerber/pdf-to-zpl
```

```php
<?php
use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\Settings\ImageScale;

// By default this will convert a 4x6 label
$converter = new PdfToZplConverter();

// Get an array of ZPL commands (1 per page)
$pages = $converter->convertFromFile("myFile.pdf");
// Or from a blob:
$pages = $converter->convertFromBlob(file_get_contents("myFile.pdf"));

foreach ($pages as $page) {
    // Each page is a single ZPL statement
    assert(str_starts_with($page, "^XA^GFA,"));
}
```

You will need admin rights to setup Imagick PDF reading (essential for this library).

## Linux Environment Setup:

The minimum version for this package is `8.1`.

Ensure you have Imagick and GD installed using:
```
sudo apt install php8.3-gd

sudo apt install php8.3-imagick
```
(Or whatever PHP Version you are using)
Then make sure to enable them in `php.ini` (usually enabled by default).

### Imagick Settings
You may need to enable PDF permission in your Imagick settings.

- Find your Imagick Policy File: `ls /etc/ | grep ImageMagick`
- Edit your Imagick Policy File: `sudo nano "/etc/ImageMagick6/policy.xml"`

Find this line and ensure the rights are set to `read | write`:
```xml
<policy domain="coder" rights="none" pattern="PDF" />
```
Change to:
```xml
<policy domain="coder" rights="read | write" pattern="PDF" />
```
If this line doesn't exist at all, add it. You'll only run into this with tiny linux boxes like Github Actions. 

Imagick has had PDF related security issues. Convert only trusted PDFs. Here's one example: [CVE-2020-29599](https://nvd.nist.gov/vuln/detail/CVE-2020-29599)

### Windows Environment Setup:
Install `GhostScript` with `choco` and Imagick and GD extensions.

```sh
choco install ghostscript
```


## Converting Images:
```php
<?php
use Faerber\PdfToZpl\ImageToZplConverter;

$converter = new ImageToZplConverter();

// Get an array of ZPL commands (1 per page)
[$zpl] = $converter->convertFromFile("myFile.png");
assert(str_starts_with($zpl, "^XA^GFA,"));
```

## Previewing Labels
The [`labelary`](https://labelary.com/) API is used to generate images from ZPL allowing the label to be previewed.
This is a free API that requires no auth so it can be used with no setup. Be sure to respect their rate limits!

```php
<?php
use Faerber\PdfToZpl\LabelImage;
use Faerber\PdfToZpl\Settings\LabelDirection;

$zpl = "^XA_ZPL_DATA_HERE...";

$labelImage = new LabelImage(
    zpl: $zpl,
    direction: LabelDirection::Up,
);
$labelImage->saveAs("my_label.png");
```

## Settings 
There are many settings you can use to configure the conversion.
You can use Imagick instead of GD, rotate and resize labels etc.

See <a href="_docs/settings.md">Settings</a> for more details.

## Unit Testing
Run `composer test`. Testing is done via PHP Unit. 

If you make major changes you may need to regenerate the test data (tests pass if each file is at least 95% similar to the test data generated with PHP 8.3 on my linux box).
For example modifying scaling code where the output is correct but the test data is outdated.
Run `composer generate-test-data` and manually verify the images are rendered correctly.

## Benchmarking
Run `composer benchmark`. Benchmarking is done via `phpbench`. 

Here's some basic performance information:
- Converting a PNG to ZPL: `668.26ms`
- Convert a 3 page PDF label to ZPL with GD Backend: `1.8s`
- Convert a 3 page PDF label to ZPL with Imagick Backend: `5.4s`

This was run on my workstation. (`AMD Ryzen 9 7950x 16-core`, `32Gib`)

See [phpbench output](.phpbench/html/index.html) for more details. 

## Formatting
Run `composer format`. Formatting is done via `php-cs-fixer`. 

## How does this work?
1. Loads the PDF and render each page as image
1. Scale the image to match the DPI and aspect ratio of the label
1. Convert each page into a grayscaled bitmap
1. Run line encode the bitmap and marshall it into a ZPL binary representation
1. Wrap the encoded data into a ZPL payload
