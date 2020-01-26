<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * The object representing an entity of the sidebar.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SidebarEntityData
{
    /**
     * The type of the entity.
     * @var string
     */
    protected $type = '';

    /**
     * The name of the entity.
     * @var string
     */
    protected $name = '';

    /**
     * The translated label of the entity.
     * @var string
     */
    protected $label = '';

    /**
     * The position of the entity in the pinned list. 0 if not pinned.
     * @var int
     */
    protected $pinnedPosition = 0;

    /**
     * The timestamp when the entity was last viewed.
     * @var DateTimeInterface
     */
    protected $lastViewTime;

    /**
     * Initializes the transfer object.
     */
    public function __construct()
    {
        $this->lastViewTime = new DateTimeImmutable();
    }

    /**
     * Sets the type of the entity.
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Returns the type of the entity.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the name of the entity.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the entity.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the translated label of the entity.
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Returns the translated label of the entity.
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Sets the position of the entity in the pinned list. 0 if not pinned.
     * @param int $pinnedPosition
     * @return $this
     */
    public function setPinnedPosition(int $pinnedPosition): self
    {
        $this->pinnedPosition = $pinnedPosition;
        return $this;
    }

    /**
     * Returns the position of the entity in the pinned list. 0 if not pinned.
     * @return int
     */
    public function getPinnedPosition(): int
    {
        return $this->pinnedPosition;
    }

    /**
     * Sets the timestamp when the entity was last viewed.
     * @param DateTimeInterface $lastViewTime
     * @return $this
     */
    public function setLastViewTime(DateTimeInterface $lastViewTime): self
    {
        $this->lastViewTime = $lastViewTime;
        return $this;
    }

    /**
     * Returns the timestamp when the entity was last viewed.
     * @return DateTimeInterface
     */
    public function getLastViewTime(): DateTimeInterface
    {
        return $this->lastViewTime;
    }
}
