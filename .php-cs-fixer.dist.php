<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['vendor'])
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'no_alternative_syntax' => false,
        'array_syntax' => ['syntax' => 'short'],
        '@Symfony:risky' => true,
        'phpdoc_no_alias_tag' => false,
        'echo_tag_syntax' => ['format' => 'short'],
        'native_function_invocation' => [
            'include' => ['@internal'],
            'scope' => 'namespaced',
            'strict' => false,
        ],
        'native_constant_invocation' => [
            'strict' => false,
        ],
        'phpdoc_to_comment' => false,
        'fopen_flags' => ['b_mode' => true],
        'php_unit_method_casing' => ['case' => 'snake_case'],
        'no_useless_concat_operator' => false,
        'class_definition' => ['multi_line_extends_each_single_line' => true],
        'single_line_throw' => false,
    ])
    ->setFinder($finder)
    ->setCacheFile('.php-cs-fixer.cache')
;
