<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Sidebar;

use Exception;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
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
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Initializes the handler.
     * @param Setting $currentSetting
     * @param SerializerInterface $portalApiServerSerializer
     */
    public function __construct(Setting $currentSetting, SerializerInterface $portalApiServerSerializer)
    {
        $this->currentSetting = $currentSetting;
        $this->serializer = $portalApiServerSerializer;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $newEntities = $this->buildNewEntities(
            $this->getExistingEntities(),
            $this->getEntitiesFromRequest($request)
        );
        $this->applyNewEntities($newEntities);

        return new EmptyResponse();
    }

    /**
     * Returns the already existing entities with mapped keys.
     * @return array<string,SidebarEntity>|SidebarEntity[]
     */
    protected function getExistingEntities(): array
    {
        $result = [];
        foreach ($this->currentSetting->getSidebarEntities() as $entity) {
            $result["{$entity->getType()}|{$entity->getName()}"] = $entity;
        }
        return $result;
    }

    /**
     * Returns the entities send in the request with mapped keys.
     * @param ServerRequestInterface $request
     * @return array<string,SidebarEntityData>|SidebarEntityData[]
     * @throws PortalApiServerException
     */
    protected function getEntitiesFromRequest(ServerRequestInterface $request): array
    {
        $result = [];
        foreach ($this->parseRequestBody($request) as $entity) {
            $result["{$entity->getType()}|{$entity->getName()}"] = $entity;
        }
        return $result;
    }

    /**
     * Parses the body of the request.
     * @param ServerRequestInterface $request
     * @return array|SidebarEntityData[]
     * @throws PortalApiServerException
     */
    protected function parseRequestBody(ServerRequestInterface $request): array
    {
        try {
            $requestBody = $request->getBody()->getContents();

            /** @var class-string<mixed> $type */
            $type = sprintf('array<%s>', SidebarEntityData::class);
            return $this->serializer->deserialize($requestBody, $type, 'json');
        } catch (Exception $e) {
            throw new InvalidRequestException($e->getMessage(), $e);
        }
    }

    /**
     * Builds and returns the new entities.
     * @param array<string,SidebarEntity>|SidebarEntity[] $existingEntities
     * @param array<string,SidebarEntityData>|SidebarEntityData[] $requestEntities
     * @return array<string,SidebarEntity>|SidebarEntity[]
     */
    protected function buildNewEntities(array $existingEntities, array $requestEntities): array
    {
        $newEntities = [];
        foreach ($requestEntities as $key => $entity) {
            $newEntity = $existingEntities[$key] ?? $this->createEntity($entity->getType(), $entity->getName());
            $this->hydrateEntity($newEntity, $entity);
            $newEntities[$key] = $newEntity;
        }
        return $newEntities;
    }

    /**
     * Creates a new sidebar entity with the specified type and name.
     * @param string $type
     * @param string $name
     * @return SidebarEntity
     */
    protected function createEntity(string $type, string $name): SidebarEntity
    {
        $result = new SidebarEntity();
        $result->setSetting($this->currentSetting)
               ->setType($type)
               ->setName($name);
        return $result;
    }

    /**
     * Hydrates the entity data.
     * @param SidebarEntity $newEntity
     * @param SidebarEntityData $requestEntity
     */
    protected function hydrateEntity(SidebarEntity $newEntity, SidebarEntityData $requestEntity): void
    {
        $newEntity->setLabel($requestEntity->getLabel())
                  ->setPinnedPosition($requestEntity->getPinnedPosition())
                  ->setLastViewTime($requestEntity->getLastViewTime());
    }

    /**
     * Applies the new entities to the current user.
     * @param array|SidebarEntity[] $newEntities
     */
    protected function applyNewEntities(array $newEntities): void
    {
        $collection = $this->currentSetting->getSidebarEntities();
        $collection->clear();
        foreach ($newEntities as $entity) {
            $collection->add($entity);
        }
    }
}
