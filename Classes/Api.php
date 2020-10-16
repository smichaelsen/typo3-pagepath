<?php
declare(strict_types=1);
namespace Smic\Pagepath;

use GuzzleHttp\Exception\RequestException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Api
{
    public static function getPagePath(int $pageId, array $parameters = [], int $languageId = 0): string
    {
        $url = self::buildFrontendRequestUrl($pageId, $parameters, $languageId);

        $frontendRequest = GeneralUtility::makeInstance(FrontendRequest::class);
        $frontendRequest->setUrl($url);
        // Send TYPO3 cookies as this may affect path generation
        $frontendRequest->addHeader('Cookie', 'fe_typo_user=' . $_COOKIE['fe_typo_user']);

        $extensionConfiguration = self::getExtensionConfiguration();
        if (isset($extensionConfiguration['authorization']['username'], $extensionConfiguration['authorization']['password'])) {
            $encodedCredentials = base64_encode(sprintf(
                '%s:%s',
                $extensionConfiguration['authorization']['username'],
                $extensionConfiguration['authorization']['password']
            ));
            $frontendRequest->addHeader('Authorization', 'Basic ' . $encodedCredentials);
        }
        if (isset($extensionConfiguration['disableSslVerification'])) {
            $frontendRequest->setVerify((bool)!$extensionConfiguration['disableSslVerification']);
        }

        try {
            $response = $frontendRequest->perform();
        } catch (RequestException $exception) {
            throw new ApiException('Request to resolver ' . $url . ' failed. (GuzzleHttp: ' . $exception->getMessage() . ')', 1554880240);
        }
        $result = $response->getBody()->getContents();

        $urlParts = parse_url($result);
        if (!is_array($urlParts)) {
            // filter_var is too strict (for example, underscore characters make it fail). So we use parse_url here for a quick check.
            throw new ApiException('Returned URL was not valid: ' . $urlParts, 1554880277);
        }

        return $result;
    }

    public static function getPagePathCached(int $pageId, array $parameters = [], int $languageId = 0): string
    {
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('pagepath');
        $entryIdentifier = hash('sha256', serialize([$pageId, $parameters]));
        if ($cache->has($entryIdentifier)) {
            return $cache->get($entryIdentifier);
        }

        $pagepath = self::getPagePath($pageId, $parameters, $languageId);
        $cache->set($entryIdentifier, $pagepath, ['pageId_' . $pageId]);
        return $pagepath;
    }

    protected static function getSiteUrl(int $pageId): string
    {
        $pageTsConfig = BackendUtility::getPagesTSconfig($pageId);
        $scheme = GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://';

        if (isset($pageTsConfig['TCEMAIN.']['previewDomain'])) {
            return $scheme . rtrim($pageTsConfig['TCEMAIN.']['previewDomain'], '/') . '/';
        }

        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId);
        $domain = (string)$site->getBase();
        if ($domain === null) {
            return GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
        }

        return $domain;
    }

    protected static function buildFrontendRequestUrl(int $pageId, array $parameters, int $languageId): string
    {
        $language = GeneralUtility::makeInstance(SiteFinder::class)
            ->getSiteByPageId($pageId)
            ->getLanguageById($languageId)
            ->getTwoLetterIsoCode();

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
            '%s/%s/?pagepath=true&data=%s&token=%s',
            $siteUrl,
            $language,
            base64_encode(json_encode($data)),
            $token
        );
        return $url;
    }

    protected static function getExtensionConfiguration(): array
    {
        return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('pagepath');
    }
}
