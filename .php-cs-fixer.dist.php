<?php

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . "/src",
        __DIR__ . "/benchmarks",
        __DIR__ . "/example",
        __DIR__ . "/tests",
    ]);

return (new PhpCsFixer\Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        '@PSR12' => false,
    ])
    ->setFinder($finder);
