<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Middleware;

use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The middleware managing the API client instance.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ApiClientMiddleware implements MiddlewareInterface
{
    /**
     * The api client.
     * @var ApiClientInterface
     */
    protected $apiClient;

    /**
     * The current user setting.
     * @var Setting
     */
    protected $currentSetting;

    /**
     * Initializes the middleware.
     * @param ApiClientInterface $apiClient
     * @param Setting $currentSetting
     */
    public function __construct(ApiClientInterface $apiClient, Setting $currentSetting)
    {
        $this->apiClient = $apiClient;
        $this->currentSetting = $currentSetting;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->apiClient->setLocale($this->currentSetting->getLocale());
        $this->apiClient->setModNames($this->currentSetting->getModNames());
        $this->apiClient->setAuthorizationToken($this->currentSetting->getApiAuthorizationToken());

        $response = $handler->handle($request);

        $this->currentSetting->setApiAuthorizationToken($this->apiClient->getAuthorizationToken());
        return $response;
    }
}
