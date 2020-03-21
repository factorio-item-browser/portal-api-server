<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Helper;

use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\Entity;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericIconRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericIconResponse;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;

/**
 * The helper class able to request the data for the icons style.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconsStyleFetcher
{
    /**
     * The api client factory.
     * @var ApiClientFactory
     */
    protected $apiClientFactory;

    /**
     * The api client.
     * @var ApiClientInterface
     */
    protected $apiClient;

    /**
     * The client request.
     * @var GenericIconRequest
     */
    protected $clientRequest;

    /**
     * The icons style builder.
     * @var IconsStyleBuilder
     */
    protected $iconsStyleBuilder;

    /**
     * Initializes the fetcher.
     * @param ApiClientFactory $apiClientFactory
     */
    public function __construct(ApiClientFactory $apiClientFactory)
    {
        $this->apiClientFactory = $apiClientFactory;
    }

    /**
     * Requests the icons to the specified types and names.
     * @param Setting $setting
     * @param NamesByTypes $namesByTypes
     * @return $this
     * @throws ApiClientException
     */
    public function request(Setting $setting, NamesByTypes $namesByTypes): self
    {
        $this->apiClient = $this->apiClientFactory->create($setting);
        $this->clientRequest = $this->createClientRequest($namesByTypes);
        $this->iconsStyleBuilder = new IconsStyleBuilder();

        $this->apiClient->sendRequest($this->clientRequest);
        return $this;
    }

    /**
     * Processes the response of the API server, building up the icons style.
     * @return IconsStyleData
     * @throws ApiClientException
     */
    public function process(): IconsStyleData
    {
        /** @var GenericIconResponse $clientResponse */
        $clientResponse = $this->apiClient->fetchResponse($this->clientRequest);
        $this->processClientResponse($clientResponse);

        $iconsStyleData = $this->createIconsStyleData();
        $this->addMissingTypesAndNames($iconsStyleData);
        return $iconsStyleData;
    }

    /**
     * Creates the client request to send.
     * @param NamesByTypes $namesByTypes
     * @return GenericIconRequest
     */
    protected function createClientRequest(NamesByTypes $namesByTypes): GenericIconRequest
    {
        $request = new GenericIconRequest();
        foreach ($namesByTypes->getValues() as $type => $names) {
            foreach ($names as $name) {
                $entity = new Entity();
                $entity->setType($type)
                       ->setName($name);

                $request->addEntity($entity);
            }
        }
        return $request;
    }

    /**
     * Processes the client response from the API server.
     * @param GenericIconResponse $clientResponse
     */
    protected function processClientResponse(GenericIconResponse $clientResponse): void
    {
        foreach ($clientResponse->getIcons() as $icon) {
            $this->iconsStyleBuilder->processIcon($icon);
        }
    }

    /**
     * Creates the icons style data from the builder.
     * @return IconsStyleData
     */
    protected function createIconsStyleData(): IconsStyleData
    {
        $iconsStyleData = new IconsStyleData();
        $iconsStyleData->setProcessedEntities($this->iconsStyleBuilder->getProcessedEntities())
                       ->setStyle($this->iconsStyleBuilder->getStyle());
        return $iconsStyleData;
    }

    /**
     * Adds all entities to the data which have been requested, but were not present in the API response.
     * @param IconsStyleData $iconsStyleData
     */
    protected function addMissingTypesAndNames(IconsStyleData $iconsStyleData): void
    {
        $processedEntities = $iconsStyleData->getProcessedEntities();
        foreach ($this->clientRequest->getEntities() as $entity) {
            if (!$processedEntities->hasValue($entity->getType(), $entity->getName())) {
                $processedEntities->addValue($entity->getType(), $entity->getName());
            }
        }
    }
}
