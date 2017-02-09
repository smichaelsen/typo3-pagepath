# Pagepath extension for TYPO3 CMS

This is an extension for the TYPO3 CMS.

## What it does

This extension provides API for other extensions to create a path to a TYPO3 page using page id and URL parameters.
For example, page with id `26` and `tx_ttnews[tt_news]=287` can be converted to `http://example.com/news/health_care_for_typo3_programmers/`.

This is especially helpful in contexts where `typolink` is not available, for example when creating frontend URLs in a backend module.
 
## How to use

To obtain a path to the page develop should use the following call: `$pagepath = tx_pagepath_api::getPagePath($pageId, $parameters);`

Page id must be integer value. Parameters can be either an array or a string. If it is a string, it must start from & character. If it is an array, format is the same as for the `\TYPO3\CMS\Core\Utility\GeneralUtility_div::implodeArrayForUrl()` function.

The following examples are all valid and equavalent:

    $parameters = '&tx_ttnews[tt_news]=123';
    $parameters = ['tx_ttnews[tt_news]' => 123];
    $parameters = ['tx_ttnews' => ['tt_news' => 123]];
    
The result will be either a fully qualified URL to the page or an empty string (meaning “No URL”).
Important! If there are many web sites in the page tree, the call should be made within the proper web site.

For example, if there are example1.com and example2.com, and Backend is open at http://example1.com/, resolving will work correctly only for example1.com. To overcome this limitation, make sure you have `config.typolinkEnableLinksAcrossDomains=1` in TypoScript setup for all sites.

## Compatibility

### Version 1.1

Developed for TYPO3 7.

TYPO3 8 was not explicitly tested yet, but *should* work. Please contact me if you need explicit TYPO3 8 support. 

### Version 1.0

The original code by Dmitry which is compatible with TYPO3 4.5 - 6.2.

## Contacts

Maintained by Sebastian Michaelsen <sebastian@michaelsen.io>

Credits to the original developer Dmitry Dulepov <dmitry.dulepov@gmail.com>
