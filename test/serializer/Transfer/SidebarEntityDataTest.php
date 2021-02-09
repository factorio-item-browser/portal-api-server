<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use DateTime;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The PHPUnit test of the SidebarEntityData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SidebarEntityDataTest extends SerializerTestCase
{
    private function getObject(): object
    {
        $object = new SidebarEntityData();
        $object->type = 'abc';
        $object->name = 'def';
        $object->label = 'ghi';
        $object->pinnedPosition = 42;
        $object->lastViewTime = new DateTime('2038-01-19 03:14:07.123');
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
    {
        return [
            'type' => 'abc',
            'name' => 'def',
            'label' => 'ghi',
            'pinnedPosition' => 42,
            'lastViewTime' => '2038-01-19T03:14:07.123+00:00',
        ];
    }

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }

    public function testDeserialize(): void
    {
        $this->assertDeserializedData($this->getObject(), $this->getData());
    }
}
