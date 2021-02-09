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
    private function getObject(): object
    {
        $object = new MachineData();
        $object->name = 'abc';
        $object->label = 'def';
        $object->craftingSpeed = 13.37;
        $object->numberOfItems = 12;
        $object->numberOfFluids = 34;
        $object->numberOfModules = 56;
        $object->energyUsage = 4.2;
        $object->energyUsageUnit = 'ghi';
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
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

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
