<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\SettingData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the SettingData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingDataTest extends SerializerTestCase
{
    private function getObject(): object
    {
        $object = new SettingData();
        $object->combinationId = 'abc';
        $object->name = 'def';
        $object->locale = 'ghi';
        $object->recipeMode = 'jkl';
        $object->status = 'mno';
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
            'locale' => 'ghi',
            'recipeMode' => 'jkl',
            'status' => 'mno',
            'isTemporary' => true,
        ];
    }

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
