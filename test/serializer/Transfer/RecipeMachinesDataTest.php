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
    private function getObject(): object
    {
        $machine1 = new MachineData();
        $machine1->name = 'abc';
        $machine1->label = 'def';
        $machine1->craftingSpeed = 13.37;
        $machine1->numberOfItems = 12;
        $machine1->numberOfFluids = 34;
        $machine1->numberOfModules = 56;
        $machine1->energyUsage = 4.2;
        $machine1->energyUsageUnit = 'ghi';

        $machine2 = new MachineData();
        $machine2->name = 'jkl';
        $machine2->label = 'mno';
        $machine2->craftingSpeed = 73.31;
        $machine2->numberOfItems = 23;
        $machine2->numberOfFluids = 45;
        $machine2->numberOfModules = 67;
        $machine2->energyUsage = 2.1;
        $machine2->energyUsageUnit = 'pqr';

        $object = new RecipeMachinesData();
        $object->results = [$machine1, $machine2];
        $object->numberOfResults = 42;
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
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

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
