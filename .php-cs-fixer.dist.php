<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . "/src",
        __DIR__ . "/benchmarks",
        __DIR__ . "/example",
        __DIR__ . "/tests",
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => false,
        'braces' => [
            'position_after_functions_and_oop_constructs' => 'same',
        ],
    ])
    ->setFinder($finder);
