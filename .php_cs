<?php

return PhpCsFixer\Config::create()
    ->setRules(array(
        '@PSR2'                  => true,
        'single_quote'           => true,
        'binary_operator_spaces' =>
        ['operators' => ['=>' => 'align_single_space_minimal']]
        ,
        'no_unused_imports'      => true,
    ))
    ->setLineEnding("\n")
;
