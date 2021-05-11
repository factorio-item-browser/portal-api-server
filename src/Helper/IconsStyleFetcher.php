<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Helper;

use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericIconRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericIconResponse;
use FactorioItemBrowser\Api\Client\Transfer\Entity;
use FactorioItemBrowser\Common\Constant\Defaults;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * The helper class able to request the data for the icons style.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconsStyleFetcher
{
    private ClientInterface $apiClient;

    public function __construct(ClientInterface $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Requests the icons to the specified types and names. The returned promise must be passed to process() when the
     * response should be awaited.
     * @param Setting $setting
     * @param NamesByTypes $namesByTypes
     * @return PromiseInterface
     * @throws ClientException
     */
    public function request(Setting $setting, NamesByTypes $namesByTypes): PromiseInterface
    {
        $request = new GenericIconRequest();
        if ($setting->getHasData()) {
            $request->combinationId = $setting->getCombination()->getId()->toString();
        } else {
            $request->combinationId = Defaults::COMBINATION_ID;
        }

        foreach ($namesByTypes->values as $type => $names) {
            foreach ($names as $name) {
                $entity = new Entity();
                $entity->type = $type;
                $entity->name = $name;
                $request->entities[] = $entity;
            }
        }
        return $this->apiClient->sendRequest($request);
    }

    /**
     * Processes the response of the API server, building up the icons style.
     * @param string $cssSelector
     * @param PromiseInterface $requestPromise
     * @return IconsStyleData
     */
    public function process(string $cssSelector, PromiseInterface $requestPromise): IconsStyleData
    {
        $builder = new IconsStyleBuilder($cssSelector);

        /** @var GenericIconResponse $response */
        $response = $requestPromise->wait();
        foreach ($response->icons as $icon) {
            $builder->processIcon($icon);
        }

        $data = new IconsStyleData();
        $data->processedEntities = clone $builder->getProcessedEntities();
        $data->style = $builder->getStyle();
        return $data;
    }

    /**
     * Adds the missing entities, which have been requested but not returned, to the processed ones.
     * @param NamesByTypes $processedEntities
     * @param NamesByTypes $requestedEntities
     */
    public function addMissingEntities(NamesByTypes $processedEntities, NamesByTypes $requestedEntities): void
    {
        foreach ($requestedEntities->values as $type => $names) {
            foreach ($names as $name) {
                if (!$processedEntities->has($type, $name)) {
                    $processedEntities->add($type, $name);
                }
            }
        }
    }
}
