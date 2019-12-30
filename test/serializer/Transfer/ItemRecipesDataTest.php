<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemRecipesData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the ItemRecipesData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemRecipesDataTest extends SerializerTestCase
{
    /**
     * Returns the object to test on.
     * @return object
     */
    protected function getObject(): object
    {
        $result1 = new EntityData();
        $result1->setType('mno')
                ->setName('pqr')
                ->setLabel('stu')
                ->setNumberOfRecipes(12);

        $result2 = new EntityData();
        $result2->setType('vwx')
                ->setName('yza')
                ->setLabel('bcd')
                ->setNumberOfRecipes(34);

        $object = new ItemRecipesData();
        $object->setType('abc')
               ->setName('def')
               ->setLabel('ghi')
               ->setDescription('jkl')
               ->setResults([$result1, $result2])
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
            'type' => 'abc',
            'name' => 'def',
            'label' => 'ghi',
            'description' => 'jkl',
            'results' => [
                [
                    'type' => 'mno',
                    'name' => 'pqr',
                    'label' => 'stu',
                    'recipes' => [],
                    'numberOfRecipes' => 12,
                ],
                [
                    'type' => 'vwx',
                    'name' => 'yza',
                    'label' => 'bcd',
                    'recipes' => [],
                    'numberOfRecipes' => 34,
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
