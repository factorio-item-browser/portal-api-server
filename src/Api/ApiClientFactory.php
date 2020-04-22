<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Api;

use Doctrine\ORM\EntityManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use Laminas\ServiceManager\ServiceManager;

/**
 * The extended factory of the API clients.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ApiClientFactory
{
    /**
     * The entity manager.
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * The service manager.
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * The data to already created clients.
     * @var array<string,array<int,Data>>|Data[][]
     */
    protected $data = [];

    /**
     * Initializes the factory.
     * @param EntityManagerInterface $entityManager
     * @param ServiceManager $serviceManager
     */
    public function __construct(EntityManagerInterface $entityManager, ServiceManager $serviceManager)
    {
        $this->entityManager = $entityManager;
        $this->serviceManager = $serviceManager;
    }

    /**
     * Creates a new instance of the API client for the setting.
     * @param Setting $setting
     * @return ApiClientInterface
     */
    public function create(Setting $setting): ApiClientInterface
    {
        return $this->createApiClient($setting, true);
    }

    /**
     * Creates a new instance of the API client for the setting, without falling back to Vanilla when data is not yet
     * available.
     * @param Setting $setting
     * @return ApiClientInterface
     */
    public function createWithoutFallback(Setting $setting): ApiClientInterface
    {
        return $this->createApiClient($setting, false);
    }

    /**
     * Actually creates a new API client for the setting, if not available yet.
     * @param Setting $setting
     * @param bool $withFallback
     * @return ApiClientInterface
     */
    protected function createApiClient(Setting $setting, bool $withFallback): ApiClientInterface
    {
        $data = $this->getData($setting, $withFallback);
        $apiClient = $data->getApiClient() ?? $this->serviceManager->build(ApiClientInterface::class);
        $this->configureApiClient($apiClient, $setting, $withFallback);

        return $apiClient;
    }

    /**
     * Configures the API client to match the setting.
     * @param ApiClientInterface $apiClient
     * @param Setting $setting
     */
    public function configure(ApiClientInterface $apiClient, Setting $setting): void
    {
        $this->configureApiClient($apiClient, $setting, true);
    }

    /**
     * Configures the API client to match the setting, without falling back to Vanilla when data is not yet available.
     * @param ApiClientInterface $apiClient
     * @param Setting $setting
     */
    public function configureWithoutFallback(ApiClientInterface $apiClient, Setting $setting): void
    {
        $this->configureApiClient($apiClient, $setting, false);
    }

    /**
     * Actually configures the API client to match the setting.
     * @param ApiClientInterface $apiClient
     * @param Setting $setting
     * @param bool $withFallback
     */
    protected function configureApiClient(ApiClientInterface $apiClient, Setting $setting, bool $withFallback): void
    {
        $useFallback = !$setting->getHasData() && $withFallback;

        $apiClient->setLocale($setting->getLocale());
        $apiClient->setModNames($useFallback ? [Constant::MOD_NAME_BASE] : $setting->getCombination()->getModNames());
        if ($useFallback !== $setting->getHasData()) {
            $apiClient->setAuthorizationToken($setting->getApiAuthorizationToken());
        }

        $data = $this->getData($setting, $withFallback);
        $data->setApiClient($apiClient)
             ->setIsFallback($useFallback);
    }

    /**
     * Returns the data to the setting and fallback behavior, creating it if not yet available.
     * @param Setting $setting
     * @param bool $withFallback
     * @return Data
     */
    protected function getData(Setting $setting, bool $withFallback): Data
    {
        $withFallback = (int) $withFallback;
        $settingId = $setting->getId()->toString();
        if (!isset($this->data[$settingId][$withFallback])) {
            $data = new Data();
            $data->setSetting($setting);

            $this->data[$settingId][$withFallback] = $data;
        }
        return $this->data[$settingId][$withFallback];
    }

    /**
     * Creates an API client instance for the specified combination of mods.
     * @param array<string> $modNames
     * @return ApiClientInterface
     */
    public function createForModNames(array $modNames): ApiClientInterface
    {
        /* @var ApiClientInterface $apiClient */
        $apiClient = $this->serviceManager->build(ApiClientInterface::class);
        $apiClient->setModNames($modNames);

        return $apiClient;
    }

    /**
     * Persists the authorization tokens into the setting entities.
     */
    public function persistAuthorizationTokens(): void
    {
        foreach ($this->data as $settingData) {
            foreach ($settingData as $data) {
                $setting = $data->getSetting();
                if (
                    $setting->getHasData() !== $data->getIsFallback()
                    && $data->getApiClient() !== null
                    && $setting->getApiAuthorizationToken() !== $data->getApiClient()->getAuthorizationToken()
                ) {
                    $setting->setApiAuthorizationToken($data->getApiClient()->getAuthorizationToken());
                    $this->entityManager->persist($setting);
                }
            }
        }
    }
}
