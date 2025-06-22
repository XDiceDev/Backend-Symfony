<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(['src', 'tests'])
    ->name('*.php')
    ->exclude(['vendor', 'var', 'config'])
;

return (new Config())
    ->setRules([
        '@PSR12' => true,
        '@PhpCsFixer' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'no_extra_blank_lines' => true,
        'no_trailing_whitespace' => true,
        'line_ending' => true,
        'single_quote' => true,
        'phpdoc_ignore_psalm_tags' => true,
        'strict_types_declarations' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_functions' => true,
        ],
    ])
    ->setLineEnding("\n")
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setCacheFile('var/.php-cs-fixer.cache')
;
