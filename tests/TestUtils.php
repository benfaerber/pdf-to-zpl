<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class TestUtils {
    public function __construct(private LoggerInterface $logger) {
    }

    /** Small things like PHP version, imagick version, etc
    * Break unit tests by creating tiny byte differences
    * The results are still valid.
    * This is how similar pages must be for them to considered ok
    */
    const PERCENT_DIFFERENCE_TOLERANCE = 95;

    public function testData(string $filename): string {
        return __DIR__ . "/../test_data/{$filename}";
    }

    public function testOutput(string $filename): string {
        return __DIR__ . "/../test_output/{$filename}";
    }

    public function fileGetContents(string $name): string {
        $data = file_get_contents($name);
        if ($data === false) {
            throw new Exception("Failed to read {$name}!");
        }
        return $data;
    }

    /** @return string[] */
    public function loadExpectedPages(string $name, int $pageCount): array {
        return array_map(
            fn ($index) => self::fileGetContents($this->testOutput("{$name}_{$index}.zpl.txt")),
            range(0, $pageCount - 1)
        );
    }

    public function isZplSimilar(array $pagesA, array $pagesB, string $context) {
        $acc = 0;
        $comps = 0;
        for ($pageNum = 0; $pageNum < count($pagesA); $pageNum++) {
            $preview = fn ($s) => substr($s, 0, 10_000);
            similar_text($preview($pagesA[$pageNum]), $preview($pagesB[$pageNum]), $percent);
            $this->logger->info("Texts are {$percent}% similar ({$context})");
            $acc += $percent;
            $comps += 1;
        }

        $avg = $acc / $comps;
        $this->logger->info("Texts are {$avg}% similar ({$context})");
        return $avg > self::PERCENT_DIFFERENCE_TOLERANCE;
    }
}
