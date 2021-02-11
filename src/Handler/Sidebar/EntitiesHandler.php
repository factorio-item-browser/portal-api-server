<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Sidebar;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler for sending the sidebar entities to the server.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class EntitiesHandler implements RequestHandlerInterface
{
    private Setting $currentSetting;
    private MapperManagerInterface $mapperManager;
    private SidebarEntitiesHelper $sidebarEntitiesHelper;

    public function __construct(
        Setting $currentSetting,
        MapperManagerInterface $mapperManager,
        SidebarEntitiesHelper $sidebarEntitiesHelper
    ) {
        $this->currentSetting = $currentSetting;
        $this->mapperManager = $mapperManager;
        $this->sidebarEntitiesHelper = $sidebarEntitiesHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var array<SidebarEntityData> $entities */
        $entities = $request->getParsedBody();
        $mappedEntities = array_map([$this, 'mapEntity'], $entities);

        $this->sidebarEntitiesHelper->replaceEntities($this->currentSetting, $mappedEntities);

        return new EmptyResponse();
    }

    private function mapEntity(SidebarEntityData $sidebarEntityData): SidebarEntity
    {
        return $this->mapperManager->map($sidebarEntityData, new SidebarEntity());
    }
}
