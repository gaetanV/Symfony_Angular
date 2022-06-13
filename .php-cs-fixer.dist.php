<?php

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PHP71Migration' => true,
        '@PhpCsFixer' => true,
        '@PSR2' => true,
        '@PSR12' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'protected_to_private' => false,
        'declare_strict_types' => false,
        'php_unit_test_class_requires_covers' => false,
        'php_unit_internal_class' => false
    ])
    ->setUsingCache(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__."/src/")
            ->in(__DIR__."/tests/")
    )
;