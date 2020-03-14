<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Entity;

use Ramsey\Uuid\UuidInterface;

/**
 * The class representing a combination.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Combination
{
    /**
     * The id of the combination.
     * @var UuidInterface
     */
    protected $id;

    /**
     * The mod names of the combination.
     * @var array|string[]
     */
    protected $modNames = [];

    /**
     * Whether data for the combination is already available.
     * @var bool
     */
    protected $isAvailable = false;

    /**
     * Sets the id of the combination.
     * @param UuidInterface $id
     * @return $this
     */
    public function setId(UuidInterface $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the id of the combination.
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * Sets the mod names of the combination.
     * @param array|string[] $modNames
     * @return $this
     */
    public function setModNames($modNames)
    {
        $this->modNames = $modNames;
        return $this;
    }

    /**
     * Returns the mod names of the combination.
     * @return array|string[]
     */
    public function getModNames()
    {
        return $this->modNames;
    }

    /**
     * Sets the data for the combination is already available.
     * @param bool $isAvailable
     * @return $this
     */
    public function setIsAvailable(bool $isAvailable): self
    {
        $this->isAvailable = $isAvailable;
        return $this;
    }

    /**
     * Returns the data for the combination is already available.
     * @return bool
     */
    public function getIsAvailable(): bool
    {
        return $this->isAvailable;
    }
}
