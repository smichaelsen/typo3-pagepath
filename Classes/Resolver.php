<?php
declare(strict_types=1);
namespace Smic\Pagepath;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class Resolver
{
    public function processRequest(ServerRequestInterface $request, ResponseInterface $response)
    {
        $getParameters = $request->getQueryParams();
        $params = json_decode(base64_decode($getParameters['data']), true);
        if (!is_array($params)) {
            return $response;
        }

        if (!TokenUtility::validateToken($params, $getParameters['token'])) {
            header('HTTP/1.0 403 Access denied');
            return $response->withStatus(403);
        }

        $url = $this->resolveUrl((int)$params['id'], (string)$params['parameters']);

        $response->getBody()->write($url);
        return $response;
    }

    public function resolveUrl($pageId, string $parameters): string
    {
        header('Content-type: text/plain; charset=iso-8859-1');
        if ($pageId === 0) {
            return '';
        }
        $this->createTSFE($pageId);

        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $typolinkConf = [
            'parameter' => $pageId,
            'useCacheHash' => !empty($parameters),
        ];
        if (!empty($parameters)) {
            $typolinkConf['additionalParams'] = $parameters;
        }
        $url = $cObj->typoLink_URL($typolinkConf);
        if (empty($url)) {
            $url = '/';
        }
        $parts = parse_url($url);
        if (empty($parts['host'])) {
            $url = GeneralUtility::locationHeaderUrl($url);
        }
        return $url;
    }

    protected function createTSFE(int $pageId): void
    {
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(TypoScriptFrontendController::class, $GLOBALS['TYPO3_CONF_VARS'], $pageId, '');

        $GLOBALS['TSFE']->connectToDB();
        $GLOBALS['TSFE']->initFEuser();
        $GLOBALS['TSFE']->determineId();
        $GLOBALS['TSFE']->initTemplate();
        $GLOBALS['TSFE']->getConfigArray();

        // Set linkVars, absRefPrefix, etc
        $GLOBALS['TSFE']->preparePageContentGeneration();
    }
}
