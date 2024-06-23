<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__.'/src',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder($finder);
