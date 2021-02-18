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
    public string $name = '';
    public string $label = '';
    public float $craftingSpeed = 0.;
    public int $numberOfItems = 0;
    public int $numberOfFluids = 0;
    public int $numberOfModules = 0;
    public float $energyUsage = 0.;
    public string $energyUsageUnit = '';
}
