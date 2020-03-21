<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class NamesByTypes
{
    /**
     * The entity names grouped by their types.
     * @var array<string,array<string>>
     */
    protected $values = [];

    /**
     * Sets the entity names grouped by their types.
     * @param array<string,array<string>> $values
     * @return $this
     */
    public function setValues(array $values): self
    {
        $this->values = $values;
        return $this;
    }

    /**
     * Adds a value to the transfer object.
     * @param string $type
     * @param string $name
     * @return $this
     */
    public function addValue(string $type, string $name): self
    {
        $this->values[$type][] = $name;
        return $this;
    }

    /**
     * Returns the entity names grouped by their types.
     * @return array<string,array<string>>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Returns whether the specified type and name is present in the transfer object.
     * @param string $type
     * @param string $name
     * @return bool
     */
    public function hasValue(string $type, string $name): bool
    {
        return in_array($name, $this->values[$type] ?? [], true);
    }
}
