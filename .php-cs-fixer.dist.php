<?php
  
$finder = (new PhpCsFixer\Finder())
    ->exclude('Resources')
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder)
;
