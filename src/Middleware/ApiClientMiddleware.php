<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Middleware;

use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
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
     * The api client factory.
     * @var ApiClientFactory
     */
    protected $apiClientFactory;

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
     * @param ApiClientFactory $apiClientFactory
     * @param ApiClientInterface $apiClient
     * @param Setting $currentSetting
     */
    public function __construct(
        ApiClientFactory $apiClientFactory,
        ApiClientInterface $apiClient,
        Setting $currentSetting
    ) {
        $this->apiClientFactory = $apiClientFactory;
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
        $this->apiClientFactory->configure($this->apiClient, $this->currentSetting);
        $response = $handler->handle($request);
        $this->apiClientFactory->persistAuthorizationTokens();
        return $response;
    }
}
