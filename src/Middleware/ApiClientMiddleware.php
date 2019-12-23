<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Middleware;

use FactorioItemBrowser\Api\Client\ApiClientInterface;
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
     * Initializes the middleware.
     * @param ApiClientInterface $apiClient
     */
    public function __construct(ApiClientInterface $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // @todo Read from user settingss
        $this->apiClient->setLocale('de');
        $this->apiClient->setModNames(['base']);

        return $handler->handle($request);
    }
}
