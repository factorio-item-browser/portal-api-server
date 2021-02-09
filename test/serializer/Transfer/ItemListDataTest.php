<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\ItemListData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemMetaData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the ItemListData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemListDataTest extends SerializerTestCase
{
    private function getObject(): object
    {
        $result1 = new ItemMetaData();
        $result1->type = 'abc';
        $result1->name = 'def';

        $result2 = new ItemMetaData();
        $result2->type = 'ghi';
        $result2->name = 'jkl';

        $object = new ItemListData();
        $object->results = [$result1, $result2];
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
                    'type' => 'abc',
                    'name' => 'def',
                ],
                [
                    'type' => 'ghi',
                    'name' => 'jkl',
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
