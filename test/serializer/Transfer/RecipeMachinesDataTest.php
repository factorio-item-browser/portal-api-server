<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\MachineData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeMachinesData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the RecipeMachinesData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeMachinesDataTest extends SerializerTestCase
{
    /**
     * Returns the object to test on.
     * @return object
     */
    protected function getObject(): object
    {
        $machine1 = new MachineData();
        $machine1->setName('abc')
                 ->setLabel('def')
                 ->setCraftingSpeed(13.37)
                 ->setNumberOfItems(12)
                 ->setNumberOfFluids(34)
                 ->setNumberOfModules(56)
                 ->setEnergyUsage(4.2)
                 ->setEnergyUsageUnit('ghi');

        $machine2 = new MachineData();
        $machine2->setName('jkl')
                 ->setLabel('mno')
                 ->setCraftingSpeed(73.31)
                 ->setNumberOfItems(23)
                 ->setNumberOfFluids(45)
                 ->setNumberOfModules(67)
                 ->setEnergyUsage(2.1)
                 ->setEnergyUsageUnit('pqr');

        $object = new RecipeMachinesData();
        $object->setResults([$machine1, $machine2])
               ->setNumberOfResults(42);
        return $object;
    }

    /**
     * Returns the data to test on.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'results' => [
                [
                    'name' => 'abc',
                    'label' => 'def',
                    'craftingSpeed' => 13.37,
                    'numberOfItems' => 12,
                    'numberOfFluids' => 34,
                    'numberOfModules' => 56,
                    'energyUsage' => 4.2,
                    'energyUsageUnit' => 'ghi',
                ],
                [
                    'name' => 'jkl',
                    'label' => 'mno',
                    'craftingSpeed' => 73.31,
                    'numberOfItems' => 23,
                    'numberOfFluids' => 45,
                    'numberOfModules' => 67,
                    'energyUsage' => 2.1,
                    'energyUsageUnit' => 'pqr',
                ],
            ],
            'numberOfResults' => 42,
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
