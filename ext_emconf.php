<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Page path',
    'description' => 'Provides an API for Backend modules to get proper frontend URLs',
    'category' => 'be',
    'author' => 'Sebastian Michaelsen',
    'author_email' => 'sebastian@michaelsen.io',
    'state' => 'stable',
    'version' => '2.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.13-10.4.99',
            'php' => '7.1.0-7.4.99',
        ],
    ],
];
