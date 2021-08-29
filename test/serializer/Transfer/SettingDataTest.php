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
        $object->combinationHash = 'def';
        $object->name = 'ghi';
        $object->locale = 'jkl';
        $object->recipeMode = 'mno';
        $object->status = 'pqr';
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
            'combinationHash' => 'def',
            'name' => 'ghi',
            'locale' => 'jkl',
            'recipeMode' => 'mno',
            'status' => 'pqr',
            'isTemporary' => true,
        ];
    }

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
