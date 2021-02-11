<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Helper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Transfer\Entity;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericDetailsResponse;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;

/**
 * The helper for managing the sidebar entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SidebarEntitiesHelper
{
    private ClientInterface $apiClient;
    private EntityManagerInterface $entityManager;
    private MapperManagerInterface $mapperManager;

    public function __construct(
        ClientInterface $apiClient,
        EntityManagerInterface $entityManager,
        MapperManagerInterface $mapperManager
    ) {
        $this->apiClient = $apiClient;
        $this->entityManager = $entityManager;
        $this->mapperManager = $mapperManager;
    }

    /**
     * @param array<SidebarEntity> $entities
     * @return array<string, SidebarEntity>
     */
    private function createAssociativeMap(array $entities): array
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
     * @param array<SidebarEntity> $newEntities
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
     * @throws ClientException
     */
    public function refreshLabels(Setting $setting): void
    {
        $request = $this->createRequest($setting);
        /** @var GenericDetailsResponse $response */
        $response = $this->apiClient->sendRequest($request)->wait();
        $this->processDetailsResponse($response, $setting);
    }

    private function createRequest(Setting $setting): GenericDetailsRequest
    {
        $request = new GenericDetailsRequest();
        $request->combinationId = $setting->getCombination()->getId()->toString();
        $request->locale = $setting->getLocale();
        foreach ($setting->getSidebarEntities() as $sidebarEntity) {
            /* @var SidebarEntity $sidebarEntity */
            $entity = new Entity();
            $entity->type = $sidebarEntity->getType();
            $entity->name = $sidebarEntity->getName();
            $request->entities[] = $entity;
        }
        return $request;
    }

    private function processDetailsResponse(GenericDetailsResponse $response, Setting $setting): void
    {
        $entities = $this->createAssociativeMap($setting->getSidebarEntities()->toArray());

        foreach ($response->entities as $responseEntity) {
            $key = "{$responseEntity->type}|{$responseEntity->name}";
            if (isset($entities[$key])) {
                $entities[$key]->setLabel($responseEntity->label);
                unset($entities[$key]);
            }
        }

        foreach ($entities as $entity) {
            $setting->getSidebarEntities()->removeElement($entity);
            $this->entityManager->remove($entity);
        }
    }
}
