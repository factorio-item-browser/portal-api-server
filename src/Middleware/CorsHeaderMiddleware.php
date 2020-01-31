<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The middleware injecting the CORS header into the response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CorsHeaderMiddleware implements MiddlewareInterface
{
    /**
     * The allowed origins to access the Portal API server.
     * @var array<string>
     */
    protected $allowedOrigins;

    /**
     * Initializes the middleware.
     * @param array<string> $allowedOrigins
     */
    public function __construct(array $allowedOrigins)
    {
        $this->allowedOrigins = $allowedOrigins;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        foreach ($this->allowedOrigins as $allowedOrigin) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $allowedOrigin);
        }
        return $response;
    }
}
