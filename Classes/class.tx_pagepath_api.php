<?php

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

/**
 * This class create frontend page address from the page id value and parameters.
 *
 * @author    Dmitry Dulepov <dmitry@typo3.org>
 * @package    TYPO3
 * @subpackage    tx_pagepath
 */
class tx_pagepath_api
{

    /**
     * Creates URL to page using page id and parameters
     *
     * @param int $pageId
     * @param string $parameters
     * @param bool $throwExceptionOnError
     * @return string Path to page or empty string
     * @throws \Smichaelsen\Pagepage\PagePathRequestFailedException
     */
    static public function getPagePath($pageId, $parameters = '', $throwExceptionOnError = false)
    {
        if (is_array($parameters)) {
            $parameters = GeneralUtility::implodeArrayForUrl('', $parameters);
        }
        $data = array(
            'id' => intval($pageId),
        );
        if ($parameters != '' && $parameters{0} == '&') {
            $data['parameters'] = $parameters;
        }
        $siteUrl = self::getSiteUrl($pageId);
        if ($siteUrl) {
            $url = $siteUrl . 'index.php?eID=pagepath&data=' . base64_encode(json_encode($data));
            // Send TYPO3 cookies as this may affect path generation
            $headers = array(
                'Cookie: fe_typo_user=' . $_COOKIE['fe_typo_user']
            );
            if ($throwExceptionOnError) {
                $report = [];
                $result = GeneralUtility::getURL($url, false, $headers, $report);
                if ($report['error'] !== 0) {
                    throw new \Smichaelsen\Pagepage\PagePathRequestFailedException('Request to obtain page path failed: (' . $report['error'] . ') ' . $report['message'], 1486649511);
                }
            } else {
                $result = GeneralUtility::getURL($url, false, $headers);
            }

            $urlParts = parse_url($result);
            if (!is_array($urlParts)) {
                // filter_var is too strict (for example, underscore characters make it fail). So we use parse_url here for a quick check.
                $result = '';
            } elseif ($result) {
                // See if we need to prepend domain part
                if ($urlParts['host'] == '') {
                    $result = rtrim($siteUrl, '/') . '/' . ltrim($result, '/');
                }
            }
        } else {
            $result = '';
        }

        return $result;
    }

    /**
     * Obtains site URL.
     *
     * @static
     * @param int $pageId
     * @return string
     */
    static protected function getSiteUrl($pageId)
    {
        $domain = BackendUtility::firstDomainRecord(BackendUtility::BEgetRootLine($pageId));
        $pageRecord = BackendUtility::getRecord('pages', $pageId);
        $scheme = is_array($pageRecord) && isset($pageRecord['url_scheme']) && $pageRecord['url_scheme'] == HttpUtility::SCHEME_HTTPS ? 'https' : 'http';
        return $domain ? $scheme . '://' . $domain . '/' : GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
    }
}
