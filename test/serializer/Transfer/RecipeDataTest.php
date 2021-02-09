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
        $ingredient1->type = 'abc';
        $ingredient1->name = 'def';
        $ingredient1->label = 'ghi';
        $ingredient1->amount = 1.2;

        $ingredient2 = new RecipeItemData();
        $ingredient2->type = 'jkl';
        $ingredient2->name = 'mno';
        $ingredient2->label = 'pqr';
        $ingredient2->amount = 3.4;

        $product1 = new RecipeItemData();
        $product1->type = 'stu';
        $product1->name = 'vwx';
        $product1->label = 'yza';
        $product1->amount = 5.6;

        $product2 = new RecipeItemData();
        $product2->type = 'bcd';
        $product2->name = 'efg';
        $product2->label = 'hij';
        $product2->amount = 7.8;

        $object = new RecipeData();
        $object->craftingTime = 13.37;
        $object->ingredients = [$ingredient1, $ingredient2];
        $object->products = [$product1, $product2];
        $object->isExpensive = true;

        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
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

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
