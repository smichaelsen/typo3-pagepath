<?php
declare(strict_types=1);
namespace Smic\Pagepath\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Smic\Pagepath\TokenUtility;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Resolver implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $pagePath = $request->getParsedBody()['pagepath'] ?? $request->getQueryParams()['pagepath'] ?? null;

        if ($pagePath === null) {
            return $handler->handle($request);
        }

        /** @var Response $response */
        $response = GeneralUtility::makeInstance(Response::class);

        $getParameters = $request->getQueryParams();
        $params = json_decode(base64_decode($getParameters['data']), true);
        if (!is_array($params)) {
            return $response;
        }

        if (!TokenUtility::validateToken($params, $getParameters['token'])) {
            header('HTTP/1.0 403 Access denied');
            return $response->withStatus(403);
        }

        $url = $this->resolveUrl((int)$params['id'], (string)$params['parameters'], $request);

        $response->getBody()->write($url);
        return $response;
    }

    public function resolveUrl($pageId, string $parameters, ServerRequestInterface $request): string
    {
        header('Content-type: text/plain; charset=iso-8859-1');
        if ($pageId === 0) {
            return '';
        }

        $this->prepareTSFE($request);

        $typolinkConf = [
            'parameter' => $pageId,
        ];

        if (!empty($parameters)) {
            $typolinkConf['additionalParams'] = $parameters;
        }

        $url = $GLOBALS['TSFE']->cObj->typoLink_URL($typolinkConf);

        if (empty($url)) {
            $url = '/';
        }
        $parts = parse_url($url);
        if (empty($parts['host'])) {
            $url = GeneralUtility::locationHeaderUrl($url);
        }
        return $url;
    }

    private function prepareTSFE(ServerRequestInterface $request): void
    {
        $GLOBALS['TSFE']->determineId($request);
        $GLOBALS['TSFE']->getConfigArray($request);
        // Set linkVars, absRefPrefix, etc
        $GLOBALS['TSFE']->preparePageContentGeneration($request);
    }
}
