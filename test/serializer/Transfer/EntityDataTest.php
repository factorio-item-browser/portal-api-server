<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the EntityData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class EntityDataTest extends SerializerTestCase
{
    /**
     * Returns the object to test on.
     * @return object
     */
    protected function getObject(): object
    {
        $recipe1 = new RecipeData();
        $recipe1->setCraftingTime(1.2)
                ->setIsExpensive(false);

        $recipe2 = new RecipeData();
        $recipe2->setCraftingTime(3.4)
                ->setIsExpensive(true);

        $object = new EntityData();
        $object->setType('abc')
               ->setName('def')
               ->setLabel('ghi')
               ->setRecipes([$recipe1, $recipe2])
               ->setNumberOfRecipes(42);
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
            'recipes' => [
                [
                    'craftingTime' => 1.2,
                    'ingredients' => [],
                    'products' => [],
                    'isExpensive' => false,
                ],
                [
                    'craftingTime' => 3.4,
                    'ingredients' => [],
                    'products' => [],
                    'isExpensive' => true,
                ],
            ],
            'numberOfRecipes' => 42,
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
