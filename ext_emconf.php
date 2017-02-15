<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Page path',
    'description' => 'Provides API for Backend modules to get a proper path to the Frontend page (simulateStatic/RealURL/CoolURI-like)',
    'category' => 'be',
    'author' => 'Sebastian Michaelsen',
    'author_email' => 'sebastian@michaelsen.io',
    'state' => 'stable',
    'version' => '1.1.1',
    'constraints' => [
        'depends' =>
            [
                'typo3' => '7.6.2-8.6.99',
                'php' => '5.5.0-7.0.99',
            ],
    ],
);
