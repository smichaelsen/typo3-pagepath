# Pagepath extension for TYPO3 CMS

This is an extension for the TYPO3 CMS.

## What it does

This extension provides an API for other extensions to create a path to a TYPO3 page for page id and additional URL parameters.

For example the page with id `26` and `tx_ttnews[tt_news]=287` can be converted to `http://example.com/news/health_care_for_typo3_programmers/`.

This is especially helpful in contexts where `typolink` is not available, for example when creating frontend URLs in a backend module.
 
## How to use

To obtain a URL use the following call:

`$pagepath = \Smic\Pagepath\Api::getPagePath($pageId, $parameters);`

Page id must be an integer value. Parameters must be an array. The format is the same as for the `\TYPO3\CMS\Core\Utility\GeneralUtility::implodeArrayForUrl()` function.

The following examples are valid and equivalent:

    $parameters = ['tx_ttnews[tt_news]' => 123];
    $parameters = ['tx_ttnews' => ['tt_news' => 123]];
    
The result will be either a fully qualified URL to the page or an \Smic\Pagepath\ApiException is thrown with further information on what went wrong.

Important! If there are many web sites in the page tree, the call should be made within the proper web site.

For example, if there are example1.com and example2.com, and Backend is open at https://example1.com/, resolving will work correctly only for example1.com. To overcome this limitation, make sure you have `config.typolinkEnableLinksAcrossDomains=1` in TypoScript setup for all sites.

## Caching

Calls to `\Smic\Pagepath\Api::getPagePath()` are not cached. For an improved performance you can use `\Smic\Pagepath\Api::getPagePathCached()`, which caches its result internally.

## Compatibility

pagepath Version | Compatible with TYPO3 version(s)
-----------------|---------------------------------
1.0              | 4.5 - 6.2
1.1              | 7.x
2.x              | 8.7
3.x              | 9.5

## Contacts

Maintained by Sebastian Michaelsen <sebastian@michaelsen.io>

Credits to the original developer Dmitry Dulepov <dmitry.dulepov@gmail.com>
