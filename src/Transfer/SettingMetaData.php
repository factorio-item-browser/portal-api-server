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
     * The id of the setting.
     * @var string
     */
    protected $id = '';

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
     * Sets the id of the setting.
     * @param string $id
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the id of the setting.
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
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
