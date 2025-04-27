# PDF to ZPL Settings

## Logging 
This library uses <a href="https://www.php-fig.org/psr/psr-3/">PSR Logging</a> which means it can integrate seamlessly into most frameworks and libraries.

To turn on logging for Laravel:
```php
<?php

use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\Images\ImageProcessorOption;

$converter = new PdfToZplConverter(new ConverterSettings(
    verboseLogs: true,
    logger: logger(), 
));
```

## Using without GD (not recommended)
If for whatever reason you can't install GD you can use Imagick only (which is default in a lot of PHP environments).
This library can work with only Imagick but GD is recommended because it's a lot faster (see [benchmarks](./.phpbench/html/index.html) for more details)! 

If you would like to only use Imagick use these settings:
```php
<?php

use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\Images\ImageProcessorOption;

$converter = new PdfToZplConverter(
    new ConverterSettings(
        imageProcessorOption: ImageProcessorOption::Imagick,
    )
);
```

## Rotating Labels:
If you are given a landscape label (ie an international label with a customs form). You can rotate it:

```php
<?php

use Faerber\PdfToZpl\PdfToZplConverter;
use Faerber\PdfToZpl\Settings\ConverterSettings;
use Faerber\PdfToZpl\Images\ImageProcessorOption;


$converter = new PdfToZplConverter(new ConverterSettings(
    rotateDegrees: 90,
));
```
