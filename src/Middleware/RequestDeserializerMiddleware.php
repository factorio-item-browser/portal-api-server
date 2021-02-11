<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Middleware;

use Exception;
use FactorioItemBrowser\Api\Client\Request\AbstractRequest;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestBodyException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use JMS\Serializer\SerializerInterface;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The middleware deserializing the request body if required.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RequestDeserializerMiddleware implements MiddlewareInterface
{
    private SerializerInterface $serializer;

    /** @var array<string, class-string<mixed>> */
    private array $requestClassesByRoutes;

    /**
     * @param SerializerInterface $portalApiServerSerializer
     * @param array<string, class-string<AbstractRequest>> $requestClassesByRoutes
     */
    public function __construct(SerializerInterface $portalApiServerSerializer, array $requestClassesByRoutes)
    {
        $this->serializer = $portalApiServerSerializer;
        $this->requestClassesByRoutes = $requestClassesByRoutes;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        $requestClass = $this->requestClassesByRoutes[$routeResult->getMatchedRouteName()] ?? '';
        if ($requestClass !== '') {
            try {
                if ($request->getHeaderLine('Content-Type') === 'application/json') {
                    $requestBody = $request->getBody()->getContents();
                } else {
                    $requestBody = '{}';
                }

                $clientRequest = $this->serializer->deserialize($requestBody, $requestClass, 'json');
                $request = $request->withParsedBody($clientRequest);
            } catch (Exception $e) {
                throw new InvalidRequestBodyException($e->getMessage(), $e);
            }
        }

        return $handler->handle($request);
    }
}
