<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Entity;

use DateTimeInterface;

/**
 * The class representing a sidebar entity in the database.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SidebarEntity
{
    /**
     * The setting the sidebar entity belongs to.
     * @var Setting
     */
    protected $setting;

    /**
     * The type of the sidebar entity.
     * @var string
     */
    protected $type = '';

    /**
     * The name of the sidebar entity.
     * @var string
     */
    protected $name = '';

    /**
     * The translated label of the sidebar entity.
     * @var string
     */
    protected $label = '';

    /**
     * The pinned position of the entity in the sidebar. 0 if not pinned.
     * @var int
     */
    protected $pinnedPosition = 0;

    /**
     * The time when the entity was last viewed.
     * @var DateTimeInterface
     */
    protected $lastViewTime;

    /**
     * Sets the setting the sidebar entity belongs to.
     * @param Setting $setting
     * @return $this
     */
    public function setSetting(Setting $setting): self
    {
        $this->setting = $setting;
        return $this;
    }

    /**
     * Returns the setting the sidebar entity belongs to.
     * @return Setting
     */
    public function getSetting(): Setting
    {
        return $this->setting;
    }

    /**
     * Sets the type of the sidebar entity.
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Returns the type of the sidebar entity.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the name of the sidebar entity.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the sidebar entity.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the translated label of the sidebar entity.
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Returns the translated label of the sidebar entity.
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Sets the pinned position of the entity in the sidebar. 0 if not pinned.
     * @param int $pinnedPosition
     * @return $this
     */
    public function setPinnedPosition(int $pinnedPosition): self
    {
        $this->pinnedPosition = $pinnedPosition;
        return $this;
    }

    /**
     * Returns the pinned position of the entity in the sidebar. 0 if not pinned.
     * @return int
     */
    public function getPinnedPosition(): int
    {
        return $this->pinnedPosition;
    }

    /**
     * Sets the time when the entity was last viewed.
     * @param DateTimeInterface $lastViewTime
     * @return $this
     */
    public function setLastViewTime(DateTimeInterface $lastViewTime): self
    {
        $this->lastViewTime = $lastViewTime;
        return $this;
    }

    /**
     * Returns the time when the entity was last viewed.
     * @return DateTimeInterface
     */
    public function getLastViewTime(): DateTimeInterface
    {
        return $this->lastViewTime;
    }
}
