<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\MachineData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the MachineData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineDataTest extends SerializerTestCase
{
    /**
     * Returns the object to test on.
     * @return object
     */
    protected function getObject(): object
    {
        $object = new MachineData();
        $object->setName('abc')
               ->setLabel('def')
               ->setCraftingSpeed(13.37)
               ->setNumberOfItems(12)
               ->setNumberOfFluids(34)
               ->setNumberOfModules(56)
               ->setEnergyUsage(4.2)
               ->setEnergyUsageUnit('ghi');
        return $object;
    }

    /**
     * Returns the data to test on.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'name' => 'abc',
            'label' => 'def',
            'craftingSpeed' => 13.37,
            'numberOfItems' => 12,
            'numberOfFluids' => 34,
            'numberOfModules' => 56,
            'energyUsage' => 4.2,
            'energyUsageUnit' => 'ghi',
        ];
    }

    /**
     * Tests the serialization.
     */
    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
