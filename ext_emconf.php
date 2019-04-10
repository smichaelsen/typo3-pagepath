<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Page path',
    'description' => 'Provides API for Backend modules to get a proper path to the Frontend page (simulateStatic/RealURL/CoolURI-like)',
    'category' => 'be',
    'author' => 'Sebastian Michaelsen',
    'author_email' => 'sebastian@michaelsen.io',
    'state' => 'stable',
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.13-8.6.99',
            'php' => '7.0.0-7.3.99',
        ],
    ],
];
