<?php
declare(strict_types=1);
namespace Smic\Pagepath;

use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendRequest
{
    protected $headers = [];

    protected $verify = true;

    protected $url = '';

    public function addHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    public function setVerify(bool $verify): void
    {
        $this->verify = $verify;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function perform() :\Psr\Http\Message\ResponseInterface
    {
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $requestConfiguration = [
            'headers' => $this->headers,
            'verify' => $this->verify,
        ];
        return $requestFactory->request(
            $this->url,
            'GET',
            $requestConfiguration
        );
    }
}
