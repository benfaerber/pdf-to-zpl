<?php

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        '@PSR12' => false,
        'braces' => [
            'position_after_functions_and_oop_constructs' => 'same',
        ],
        'no_unused_imports' => true,
    ])
    ->setFinder($finder);
