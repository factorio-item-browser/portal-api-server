<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the SettingMetaData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingMetaDataTest extends SerializerTestCase
{
    /**
     * Returns the object to test on.
     * @return object
     */
    protected function getObject(): object
    {
        $object = new SettingMetaData();
        $object->setCombinationId('abc')
               ->setName('def')
               ->setStatus('ghi');

        return $object;
    }

    /**
     * Returns the data to test on.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'combinationId' => 'abc',
            'name' => 'def',
            'status' => 'ghi',
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
