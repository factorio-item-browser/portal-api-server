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
     * The API clients and their corresponding settings.
     * @var array<mixed>
     */
    protected $clientsAndSettings = [];

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
        $apiClient = $this->serviceManager->build(ApiClientInterface::class);
        $this->configure($apiClient, $setting);
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

        $this->clientsAndSettings[] = [$apiClient, $setting];
    }

    /**
     * Persists the authorization tokens into the setting entities.
     */
    public function persistAuthorizationTokens(): void
    {
        foreach ($this->clientsAndSettings as [$apiClient, $setting]) {
            /** @var ApiClientInterface $apiClient */
            /** @var Setting $setting */
            $authorizationToken = $apiClient->getAuthorizationToken();
            if ($setting->getApiAuthorizationToken() !== $authorizationToken) {
                $setting->setApiAuthorizationToken($authorizationToken);
                $this->entityManager->persist($setting);
            }
        }
    }
}
