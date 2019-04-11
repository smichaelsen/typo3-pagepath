<?php
declare(strict_types=1);
namespace Smic\Pagepath;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Api
{
    public static function getPagePath(int $pageId, array $parameters = []): string
    {
        $parametersString = GeneralUtility::implodeArrayForUrl('', $parameters);
        $data = [
            'id' => $pageId,
        ];
        if (!empty($parametersString) && $parametersString[0] === '&') {
            $data['parameters'] = $parametersString;
        }
        $siteUrl = self::getSiteUrl($pageId);
        if (empty($siteUrl)) {
            throw new ApiException('Domain for page ' . $pageId . ' could not be determined.', 1554880211);
        }
        $token = TokenUtility::createToken($data);

        $url = sprintf(
            '%sindex.php?eID=pagepath&data=%s&token=%s',
            $siteUrl,
            base64_encode(json_encode($data)),
            $token
        );

        // Send TYPO3 cookies as this may affect path generation
        $headers = [
            'Cookie: fe_typo_user=' . $_COOKIE['fe_typo_user'],
        ];

        $report = [];
        $result = GeneralUtility::getUrl($url, 0, $headers, $report);

        if ($result === false) {
            throw new ApiException('Request to resolver ' . $url . ' failed. (' . $report['lib'] . ': ' . $report['message'] . ')', 1554880240);
        }
        $urlParts = parse_url($result);
        if (!is_array($urlParts)) {
            // filter_var is too strict (for example, underscore characters make it fail). So we use parse_url here for a quick check.
            throw new ApiException('Returned URL was not valid: ' . $urlParts, 1554880277);
        }

        // See if we need to prepend domain part
        if ($urlParts['host'] == '') {
            $result = rtrim($siteUrl, '/') . '/' . ltrim($result, '/');
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
    protected static function getSiteUrl(int $pageId): string
    {
        $pageTsConfig = BackendUtility::getPagesTSconfig($pageId);
        $scheme = GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://';

        if (isset($pageTsConfig['TCEMAIN.']['previewDomain'])) {
            return $scheme . rtrim($pageTsConfig['TCEMAIN.']['previewDomain'], '/') . '/';
        }

        $domain = BackendUtility::firstDomainRecord(BackendUtility::BEgetRootLine($pageId));
        if ($domain === null) {
            return GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
        }

        return $scheme . $domain . '/';
    }
}
