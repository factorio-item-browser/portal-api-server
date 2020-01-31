<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Session;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SessionInitData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler for initializing the session.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class InitHandler implements RequestHandlerInterface
{
    /**
     * The current user setting.
     * @var Setting
     */
    protected $currentSetting;

    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * Initializes the handler.
     * @param Setting $currentSetting
     * @param MapperManagerInterface $mapperManager
     */
    public function __construct(Setting $currentSetting, MapperManagerInterface $mapperManager)
    {
        $this->currentSetting = $currentSetting;
        $this->mapperManager = $mapperManager;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = new SessionInitData();
        $data->setSidebarEntities($this->getCurrentSidebarEntities());

        return new TransferResponse($data);
    }

    /**
     * Returns the current sidebar entities.
     * @return array<SidebarEntityData>
     */
    protected function getCurrentSidebarEntities(): array
    {
        return array_map([$this, 'mapSidebarEntity'], $this->currentSetting->getSidebarEntities()->toArray());
    }

    /**
     * Maps the sidebar entity.
     * @param SidebarEntity $sidebarEntity
     * @return SidebarEntityData
     * @throws PortalApiServerException
     */
    protected function mapSidebarEntity(SidebarEntity $sidebarEntity): SidebarEntityData
    {
        $sidebarEntityData = new SidebarEntityData();
        try {
            $this->mapperManager->map($sidebarEntity, $sidebarEntityData);
        } catch (MapperException $e) {
            throw new MappingException($e);
        }
        return $sidebarEntityData;
    }
}
