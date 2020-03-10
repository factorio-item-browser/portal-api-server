<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Api;

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
     * The service manager.
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Initializes the factory.
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
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
        $apiClient->setModNames($setting->getModNames());
        $apiClient->setAuthorizationToken($setting->getApiAuthorizationToken());
    }
}
