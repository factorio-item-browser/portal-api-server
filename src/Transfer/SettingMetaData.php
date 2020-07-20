<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The class representing the meta data of a setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingMetaData
{
    /**
     * The id of the combination used in the setting.
     * @var string
     */
    protected $combinationId = '';

    /**
     * The name of the setting.
     * @var string
     */
    protected $name = '';

    /**
     * The status of the setting.
     * @var string
     */
    protected $status = '';

    /**
     * Sets the id of the combination used in the setting.
     * @param string $combinationId
     * @return $this
     */
    public function setCombinationId(string $combinationId): self
    {
        $this->combinationId = $combinationId;
        return $this;
    }

    /**
     * Returns the id of the combination used in the setting.
     * @return string
     */
    public function getCombinationId(): string
    {
        return $this->combinationId;
    }

    /**
     * Sets the name of the setting.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the setting.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the status of the setting.
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Returns the status of the setting.
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
