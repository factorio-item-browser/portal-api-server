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
    /**
     * Returns the object to test on.
     * @return object
     */
    protected function getObject(): object
    {
        $result1 = new ItemMetaData();
        $result1->setType('abc')
                ->setName('def');

        $result2 = new ItemMetaData();
        $result2->setType('ghi')
                ->setName('jkl');

        $object = new ItemListData();
        $object->setResults([$result1, $result2])
               ->setNumberOfResults(42);
        return $object;
    }

    /**
     * Returns the data to test on.
     * @return array<mixed>
     */
    protected function getData(): array
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

    /**
     * Tests the serialization.
     */
    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
