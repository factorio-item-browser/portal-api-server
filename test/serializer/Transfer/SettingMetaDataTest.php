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
    private function getObject(): object
    {
        $object = new SettingMetaData();
        $object->combinationId = 'abc';
        $object->name = 'def';
        $object->status = 'ghi';
        $object->isTemporary = true;
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
    {
        return [
            'combinationId' => 'abc',
            'name' => 'def',
            'status' => 'ghi',
            'isTemporary' => true,
        ];
    }

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
