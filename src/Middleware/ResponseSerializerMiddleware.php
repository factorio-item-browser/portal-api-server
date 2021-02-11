<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Middleware;

use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The middleware serializing the response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ResponseSerializerMiddleware implements MiddlewareInterface
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $portalApiServerSerializer)
    {
        $this->serializer = $portalApiServerSerializer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($response instanceof TransferResponse) {
            $response = $response->withSerializer($this->serializer);
        }
        return $response;
    }
}
