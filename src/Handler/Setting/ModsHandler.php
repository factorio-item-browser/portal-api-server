<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Setting;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\Api\Client\Transfer\Mod;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSettingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * The handler for requesting the mods of a setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModsHandler implements RequestHandlerInterface
{
    private ClientInterface $apiClient;
    private User $currentUser;
    private MapperManagerInterface $mapperManager;

    public function __construct(ClientInterface $apiClient, User $currentUser, MapperManagerInterface $mapperManager)
    {
        $this->apiClient = $apiClient;
        $this->currentUser = $currentUser;
        $this->mapperManager = $mapperManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $combinationId = Uuid::fromString($request->getAttribute('combination-id', ''));
        $setting = $this->currentUser->getSettingByCombinationId($combinationId);
        if ($setting === null) {
            throw new MissingSettingException($combinationId);
        }

        $modListResponse = $this->fetchData($setting);
        $mods = array_map([$this, 'mapMod'], $modListResponse->mods);
        return new TransferResponse($mods);
    }

    /**
     * @param Setting $setting
     * @return ModListResponse
     * @throws FailedApiRequestException
     */
    private function fetchData(Setting $setting): ModListResponse
    {
        $request = new ModListRequest();
        $request->combinationId = $setting->getCombination()->getId()->toString();
        $request->locale = $setting->getLocale();

        try {
            /** @var ModListResponse $response */
            $response = $this->apiClient->sendRequest($request)->wait();
            return $response;
        } catch (ClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }

    private function mapMod(Mod $mod): ModData
    {
        return $this->mapperManager->map($mod, new ModData());
    }
}
