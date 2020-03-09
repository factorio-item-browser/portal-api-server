<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DetailsHandler
{
    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * Initializes the handler.
     * @param MapperManagerInterface $mapperManager
     */
    public function __construct(MapperManagerInterface $mapperManager)
    {
        $this->mapperManager = $mapperManager;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {


        return new TransferResponse([]);
    }
}
