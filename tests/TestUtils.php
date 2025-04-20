<?php

declare(strict_types=1);

class TestUtils {
    public static function testData(string $filename): string {
        return __DIR__ . "/../test_data/{$filename}";
    }

    public static function testOutput(string $filename): string {
        return __DIR__ . "/../test_output/{$filename}";
    }

    /** @return string[] */
    public static function loadExpectedPages(string $name, int $pageCount): array {
        return array_map(
            fn ($index) => file_get_contents(TestUtils::testOutput("{$name}_{$index}.zpl.txt")),
            range(0, $pageCount - 1)
        );
    }
}
