<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Api;

use Doctrine\ORM\EntityManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
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
     * The API clients which where created.
     * @var array<string,ApiClientInterface>|ApiClientInterface[]
     */
    protected $apiClients = [];

    /**
     * The settings which were used to create the clients.
     * @var array<string,Setting>|Setting[]
     */
    protected $settings = [];

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
        $settingId = $setting->getId()->toString();
        $apiClient = $this->apiClients[$settingId] ?? $this->serviceManager->build(ApiClientInterface::class);
        $this->configure($apiClient, $setting);
        return $apiClient;
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
     * Configures the API client to match the setting.
     * @param ApiClientInterface $apiClient
     * @param Setting $setting
     */
    public function configure(ApiClientInterface $apiClient, Setting $setting): void
    {
        $apiClient->setLocale($setting->getLocale());
        $apiClient->setModNames($setting->getCombination()->getModNames());
        $apiClient->setAuthorizationToken($setting->getApiAuthorizationToken());

        $settingId = $setting->getId()->toString();
        $this->apiClients[$settingId] = $apiClient;
        $this->settings[$settingId] = $setting;
    }

    /**
     * Persists the authorization tokens into the setting entities.
     */
    public function persistAuthorizationTokens(): void
    {
        foreach ($this->apiClients as $settingId => $apiClient) {
            $setting = $this->settings[$settingId];
            $authorizationToken = $apiClient->getAuthorizationToken();
            if ($setting->getApiAuthorizationToken() !== $authorizationToken) {
                $setting->setApiAuthorizationToken($authorizationToken);
                $this->entityManager->persist($setting);
            }
        }
    }
}
