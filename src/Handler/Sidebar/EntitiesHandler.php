<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Sidebar;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use JMS\Serializer\SerializerInterface;
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
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * The sidebar entities helper.
     * @var SidebarEntitiesHelper
     */
    protected $sidebarEntitiesHelper;

    /**
     * Initializes the handler.
     * @param Setting $currentSetting
     * @param MapperManagerInterface $mapperManager
     * @param SerializerInterface $portalApiServerSerializer
     * @param SidebarEntitiesHelper $sidebarEntitiesHelper
     */
    public function __construct(
        Setting $currentSetting,
        MapperManagerInterface $mapperManager,
        SerializerInterface $portalApiServerSerializer,
        SidebarEntitiesHelper $sidebarEntitiesHelper
    ) {
        $this->currentSetting = $currentSetting;
        $this->mapperManager = $mapperManager;
        $this->serializer = $portalApiServerSerializer;
        $this->sidebarEntitiesHelper = $sidebarEntitiesHelper;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $newEntities = $this->parseRequestBody($request);
        $this->sidebarEntitiesHelper->replaceEntities($this->currentSetting, $newEntities);

        return new EmptyResponse();
    }

    /**
     * Parses the body of the request.
     * @param ServerRequestInterface $request
     * @return array|SidebarEntity[]
     * @throws PortalApiServerException
     */
    protected function parseRequestBody(ServerRequestInterface $request): array
    {
        try {
            $requestBody = $request->getBody()->getContents();

            /** @var class-string<mixed> $type */
            $type = sprintf('array<%s>', SidebarEntityData::class);
            $entities = $this->serializer->deserialize($requestBody, $type, 'json');
            return array_map([$this, 'mapEntity'], $entities);
        } catch (Exception $e) {
            throw new InvalidRequestException($e->getMessage(), $e);
        }
    }

    /**
     * Maps the sidebar entity data to a database entity instance.
     * @param SidebarEntityData $sidebarEntityData
     * @return SidebarEntity
     * @throws MapperException
     */
    protected function mapEntity(SidebarEntityData $sidebarEntityData): SidebarEntity
    {
        $sidebarEntity = new SidebarEntity();
        $this->mapperManager->map($sidebarEntityData, $sidebarEntity);
        return $sidebarEntity;
    }
}
