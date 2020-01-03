<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The class representing the data of a machine.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineData
{
    /**
     * The name of the machine.
     * @var string
     */
    protected $name = '';

    /**
     * The translated label of the machine.
     * @var string
     */
    protected $label = '';

    /**
     * The crafting speed of the machine.
     * @var float
     */
    protected $craftingSpeed = 0.;

    /**
     * The number of items supported by the machine. 255 for unlimited.
     * @var int
     */
    protected $numberOfItems = 0;

    /**
     * The number of fluids supported by the machine.
     * @var int
     */
    protected $numberOfFluids = 0;

    /**
     * The number of module slots the machine has.
     * @var int
     */
    protected $numberOfModules = 0;

    /**
     * The energy usage of the machine.
     * @var float
     */
    protected $energyUsage = 0.;

    /**
     * The unit of the energyUsage value.
     * @var string
     */
    protected $energyUsageUnit = '';

    /**
     * Sets the name of the machine.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the machine.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the translated label of the machine.
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Returns the translated label of the machine.
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Sets the crafting speed of the machine.
     * @param float $craftingSpeed
     * @return $this
     */
    public function setCraftingSpeed(float $craftingSpeed): self
    {
        $this->craftingSpeed = $craftingSpeed;
        return $this;
    }

    /**
     * Returns the crafting speed of the machine.
     * @return float
     */
    public function getCraftingSpeed(): float
    {
        return $this->craftingSpeed;
    }

    /**
     * Sets the number of items supported by the machine. 255 for unlimited.
     * @param int $numberOfItems
     * @return $this
     */
    public function setNumberOfItems(int $numberOfItems): self
    {
        $this->numberOfItems = $numberOfItems;
        return $this;
    }

    /**
     * Returns the number of items supported by the machine. 255 for unlimited.
     * @return int
     */
    public function getNumberOfItems(): int
    {
        return $this->numberOfItems;
    }

    /**
     * Sets the number of fluids supported by the machine.
     * @param int $numberOfFluids
     * @return $this
     */
    public function setNumberOfFluids(int $numberOfFluids): self
    {
        $this->numberOfFluids = $numberOfFluids;
        return $this;
    }

    /**
     * Returns the number of fluids supported by the machine.
     * @return int
     */
    public function getNumberOfFluids(): int
    {
        return $this->numberOfFluids;
    }

    /**
     * Sets the number of module slots the machine has.
     * @param int $numberOfModules
     * @return $this
     */
    public function setNumberOfModules(int $numberOfModules): self
    {
        $this->numberOfModules = $numberOfModules;
        return $this;
    }

    /**
     * Returns number of module slots the machine has.
     * @return int
     */
    public function getNumberOfModules(): int
    {
        return $this->numberOfModules;
    }

    /**
     * Sets the energy usage of the machine.
     * @param float $energyUsage
     * @return $this
     */
    public function setEnergyUsage(float $energyUsage): self
    {
        $this->energyUsage = $energyUsage;
        return $this;
    }

    /**
     * Returns the energy usage of the machine.
     * @return float
     */
    public function getEnergyUsage(): float
    {
        return $this->energyUsage;
    }

    /**
     * Sets the unit of the energyUsage value.
     * @param string $energyUsageUnit
     * @return $this
     */
    public function setEnergyUsageUnit(string $energyUsageUnit): self
    {
        $this->energyUsageUnit = $energyUsageUnit;
        return $this;
    }

    /**
     * Returns the unit of the energyUsage value.
     * @return string
     */
    public function getEnergyUsageUnit(): string
    {
        return $this->energyUsageUnit;
    }
}
