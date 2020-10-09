<?php

use Smic\Pagepath\Middleware\Resolver;

return [
    'frontend' => [
        'pagepath/preview' => [
            'target' => Resolver::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering'
            ],
            'before' => [
                'typo3/cms-frontend/shortcut-and-mountpoint-redirect'
            ],
        ],
    ],
];
