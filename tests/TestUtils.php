<?php

declare(strict_types=1);

class TestUtils {
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
}
