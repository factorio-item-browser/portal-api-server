<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeItemData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the RecipeData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeDataTest extends SerializerTestCase
{
    /**
     * Returns the object to test on.
     * @return object
     */
    protected function getObject(): object
    {
        $ingredient1 = new RecipeItemData();
        $ingredient1->setType('abc')
                    ->setName('def')
                    ->setLabel('ghi')
                    ->setAmount(1.2);

        $ingredient2 = new RecipeItemData();
        $ingredient2->setType('jkl')
                    ->setName('mno')
                    ->setLabel('pqr')
                    ->setAmount(3.4);

        $product1 = new RecipeItemData();
        $product1->setType('stu')
                 ->setName('vwx')
                 ->setLabel('yza')
                 ->setAmount(5.6);

        $product2 = new RecipeItemData();
        $product2->setType('bcd')
                 ->setName('efg')
                 ->setLabel('hij')
                 ->setAmount(7.8);

        $object = new RecipeData();
        $object->setCraftingTime(13.37)
               ->setIngredients([$ingredient1, $ingredient2])
               ->setProducts([$product1, $product2])
               ->setIsExpensive(true);

        return $object;
    }

    /**
     * Returns the data to test on.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'craftingTime' => 13.37,
            'ingredients' => [
                [
                    'type' => 'abc',
                    'name' => 'def',
                    'label' => 'ghi',
                    'amount' => 1.2,
                ],
                [
                    'type' => 'jkl',
                    'name' => 'mno',
                    'label' => 'pqr',
                    'amount' => 3.4,
                ],
            ],
            'products' => [
                [
                    'type' => 'stu',
                    'name' => 'vwx',
                    'label' => 'yza',
                    'amount' => 5.6,
                ],
                [
                    'type' => 'bcd',
                    'name' => 'efg',
                    'label' => 'hij',
                    'amount' => 7.8,
                ],
            ],
            'isExpensive' => true,
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
