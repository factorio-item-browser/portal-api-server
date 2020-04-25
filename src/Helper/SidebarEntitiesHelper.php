<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Helper;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\Entity;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericDetailsResponse;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;

/**
 * The helper for managing the sidebar entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SidebarEntitiesHelper
{
    /**
     * The api client factory.
     * @var ApiClientFactory
     */
    protected $apiClientFactory;

    /**
     * The entity manager.
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * Initializes the helper.
     * @param ApiClientFactory $apiClientFactory
     * @param EntityManagerInterface $entityManager
     * @param MapperManagerInterface $mapperManager
     */
    public function __construct(
        ApiClientFactory $apiClientFactory,
        EntityManagerInterface $entityManager,
        MapperManagerInterface $mapperManager
    ) {
        $this->apiClientFactory = $apiClientFactory;
        $this->entityManager = $entityManager;
        $this->mapperManager = $mapperManager;
    }

    /**
     * Creates an associative map of the entities.
     * @param array|SidebarEntity[] $entities
     * @return array<string,SidebarEntity>|SidebarEntity[]
     */
    protected function createAssociativeMap(array $entities): array
    {
        $result = [];
        foreach ($entities as $entity) {
            $result["{$entity->getType()}|{$entity->getName()}"] = $entity;
        }
        return $result;
    }

    /**
     * Replaces the entities in the setting.
     * @param Setting $setting
     * @param array<SidebarEntity>|SidebarEntity[] $newEntities
     */
    public function replaceEntities(Setting $setting, array $newEntities): void
    {
        $mappedOldEntities = $this->createAssociativeMap($setting->getSidebarEntities()->toArray());
        $mappedNewEntities = $this->createAssociativeMap($newEntities);

        // Update already existing entities
        foreach (array_intersect(array_keys($mappedOldEntities), array_keys($mappedNewEntities)) as $key) {
            $newEntity = $mappedNewEntities[$key];
            $oldEntity = $mappedOldEntities[$key];
            $oldEntity->setLabel($newEntity->getLabel())
                      ->setLastViewTime($newEntity->getLastViewTime())
                      ->setPinnedPosition($newEntity->getPinnedPosition());
            $this->entityManager->persist($oldEntity);
        }

        // Remove no longer existing entities
        foreach (array_diff(array_keys($mappedOldEntities), array_keys($mappedNewEntities)) as $key) {
            $oldEntity = $mappedOldEntities[$key];
            $setting->getSidebarEntities()->removeElement($oldEntity);
            $this->entityManager->remove($oldEntity);
        }

        // Add new entities
        foreach (array_diff(array_keys($mappedNewEntities), array_keys($mappedOldEntities)) as $key) {
            $newEntity = $mappedNewEntities[$key];
            $newEntity->setSetting($setting);
            $setting->getSidebarEntities()->add($newEntity);
            $this->entityManager->persist($newEntity);
        }
    }

    /**
     * Refreshes labels of all sidebar entities.
     * @param Setting $setting
     * @throws FailedApiRequestException
     */
    public function refreshLabels(Setting $setting): void
    {
        $request = $this->createDetailsRequest($setting->getSidebarEntities()->toArray());

        $client = $this->apiClientFactory->create($setting);
        try {
            /** @var GenericDetailsResponse $response */
            $response = $client->fetchResponse($request);
            $this->processDetailsResponse($response, $setting);
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }

    /**
     * Creates the generic details request to the specified entities.
     * @param array<SidebarEntity>|SidebarEntity[] $entities
     * @return GenericDetailsRequest
     */
    protected function createDetailsRequest(array $entities): GenericDetailsRequest
    {
        $request = new GenericDetailsRequest();
        foreach ($entities as $entity) {
            $requestEntity = new Entity();
            $requestEntity->setType($entity->getType())
                          ->setName($entity->getName());
            $request->addEntity($requestEntity);
        }
        return $request;
    }

    /**
     * Processes the details response.
     * @param GenericDetailsResponse $response
     * @param Setting $setting
     */
    protected function processDetailsResponse(GenericDetailsResponse $response, Setting $setting): void
    {
        $entities = $this->createAssociativeMap($setting->getSidebarEntities()->toArray());

        foreach ($response->getEntities() as $responseEntity) {
            $key = "{$responseEntity->getType()}|{$responseEntity->getName()}";
            if (isset($entities[$key])) {
                $entities[$key]->setLabel($responseEntity->getLabel());
                unset($entities[$key]);
            }
        }

        foreach ($entities as $entity) {
            $setting->getSidebarEntities()->removeElement($entity);
            $this->entityManager->remove($entity);
        }
    }

    /**
     * Maps the entities to data objects.
     * @param array<SidebarEntity>|SidebarEntity[] $sidebarEntities
     * @return array<SidebarEntityData>|SidebarEntityData[]
     * @throws MappingException
     */
    public function mapEntities(array $sidebarEntities): array
    {
        try {
            $result = [];
            foreach ($sidebarEntities as $sidebarEntity) {
                $data = new SidebarEntityData();
                $this->mapperManager->map($sidebarEntity, $data);
                $result[] = $data;
            }
            return $result;
        } catch (MapperException $e) {
            throw new MappingException($e);
        }
    }
}
