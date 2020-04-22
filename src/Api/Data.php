<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Api;

use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;

/**
 * A plain data object used to store some properties in the factory.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Data
{
    /**
     * The setting used.
     * @var Setting
     */
    protected $setting;

    /**
     * The API client used for the setting.
     * @var ApiClientInterface|null
     */
    protected $apiClient;

    /**
     * Whether we fell back to Vanilla due to missing data.
     * @var bool
     */
    protected $isFallback = false;

    /**
     * Sets the setting used.
     * @param Setting $setting
     * @return $this
     */
    public function setSetting(Setting $setting): self
    {
        $this->setting = $setting;
        return $this;
    }

    /**
     * Returns the setting used.
     * @return Setting
     */
    public function getSetting(): Setting
    {
        return $this->setting;
    }

    /**
     * Sets the API client used for the setting.
     * @param ApiClientInterface|null $apiClient
     * @return $this
     */
    public function setApiClient(?ApiClientInterface $apiClient): self
    {
        $this->apiClient = $apiClient;
        return $this;
    }

    /**
     * Returns the API client used for the setting.
     * @return ApiClientInterface|null
     */
    public function getApiClient(): ?ApiClientInterface
    {
        return $this->apiClient;
    }

    /**
     * Sets whether we we fell back to Vanilla due to missing data.
     * @param bool $isFallback
     * @return $this
     */
    public function setIsFallback(bool $isFallback): self
    {
        $this->isFallback = $isFallback;
        return $this;
    }

    /**
     * Returns whether we fell back to Vanilla due to missing data.
     * @return bool
     */
    public function getIsFallback(): bool
    {
        return $this->isFallback;
    }
}
