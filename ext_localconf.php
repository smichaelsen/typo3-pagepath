<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'][$_EXTKEY] = \Smic\Pagepath\Resolver::class . '::processRequest';

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['pagepath'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['pagepath'] = [
        'groups' => ['pages'],
    ];
}
