<?php

declare(strict_types=1);

use Illuminate\Support\Collection;

class TestUtils {
    /** Small things like PHP version, imagick version, etc
    * Break unit tests by creating tiny byte differences
    * The results are still valid.
    * This is how similar pages must be for them to considered ok
    */
    const PERCENT_DIFFERENCE_TOLERANCE = 95;
    
    public static function testData(string $filename): string {
        return __DIR__ . "/../test_data/{$filename}";
    }

    public static function testOutput(string $filename): string {
        return __DIR__ . "/../test_output/{$filename}";
    }

    public static function fileGetContents(string $name): string {
        $data = file_get_contents($name);
        if ($data === false) {
            throw new Exception("Failed to read {$name}!");
        }
        return $data;
    } 

    /** @return string[] */
    public static function loadExpectedPages(string $name, int $pageCount): array {
        return array_map(
            fn ($index) => self::fileGetContents(TestUtils::testOutput("{$name}_{$index}.zpl.txt")),
            range(0, $pageCount - 1)
        );
    }
    

    public static function isZplSimilar(array $aPages, array $bPages) {
        $acc = 0;
        $comps = 0;
        for ($i = 0; $i < count($aPages); $i++) {
            $linesA = explode(",", $aPages[$i]);
            $linesB = explode(",", $bPages[$i]);
            for ($j = 0; $j < count($linesA); $j++) {
                similar_text($linesA[$j], $linesB[$j], $percent);
                $acc += $percent;
                $comps += 1;
            }
        }

        $avg = $acc / $comps;
        echo $avg;
        return $avg > self::PERCENT_DIFFERENCE_TOLERANCE;
    }
}
