<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Middleware;

use FactorioItemBrowser\Api\Client\ClientInterface;
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
    private ClientInterface $apiClient;
    private Setting $currentSetting;

    public function __construct(ClientInterface $apiClient, Setting $currentSetting)
    {
        $this->apiClient = $apiClient;
        $this->currentSetting = $currentSetting;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->apiClient->setDefaults(
            $this->currentSetting->getCombination()->getId()->toString(),
            $this->currentSetting->getLocale(),
        );

        return $handler->handle($request);
    }
}
