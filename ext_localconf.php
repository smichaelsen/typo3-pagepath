<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'][$_EXTKEY] = \Smic\Pagepath\Resolver::class . '::processRequest';
