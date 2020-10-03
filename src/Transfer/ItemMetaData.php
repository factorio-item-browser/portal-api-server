<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The meta data of an item.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemMetaData
{
    /**
     * The type of the entity.
     * @var string
     */
    protected $type = '';

    /**
     * The internal name of the entity.
     * @var string
     */
    protected $name = '';

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
     * Sets the internal name of the entity.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the internal name of the entity.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
