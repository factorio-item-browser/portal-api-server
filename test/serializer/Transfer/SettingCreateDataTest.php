<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\SettingCreateData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the SettingCreateData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingCreateDataTest extends SerializerTestCase
{
    private function getObject(): object
    {
        $object = new SettingCreateData();
        $object->name = 'abc';
        $object->locale = 'def';
        $object->recipeMode = 'ghi';
        $object->modNames = ['jkl', 'mno'];
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
    {
        return [
            'name' => 'abc',
            'locale' => 'def',
            'recipeMode' => 'ghi',
            'modNames' => ['jkl', 'mno'],
        ];
    }

    public function testSerialize(): void
    {
        $this->assertDeserializedData($this->getObject(), $this->getData());
    }
}
