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
        'declare_strict_types' => false
    ])
    ->setUsingCache(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
    )
;