<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use DateTimeImmutable;
use Exception;
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
    /**
     * Returns the object to test on.
     * @return object
     * @throws Exception
     */
    protected function getObject(): object
    {
        $object = new SidebarEntityData();
        $object->setType('abc')
               ->setName('def')
               ->setLabel('ghi')
               ->setPinnedPosition(42)
               ->setLastViewTime(new DateTimeImmutable('2038-01-19 03:14:07.123'));
        return $object;
    }

    /**
     * Returns the data to test on.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'type' => 'abc',
            'name' => 'def',
            'label' => 'ghi',
            'pinnedPosition' => 42,
            'lastViewTime' => '2038-01-19T03:14:07.123+00:00',
        ];
    }

    /**
     * Tests the serialization.
     * @throws Exception
     */
    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }

    /**
     * Tests the deserialization.
     * @throws Exception
     */
    public function testDeserialize(): void
    {
        $this->assertDeserializedData($this->getObject(), $this->getData());
    }
}
