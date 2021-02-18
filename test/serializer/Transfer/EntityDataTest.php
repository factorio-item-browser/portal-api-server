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
    private function getObject(): object
    {
        $recipe1 = new RecipeData();
        $recipe1->craftingTime = 1.2;
        $recipe1->isExpensive = false;

        $recipe2 = new RecipeData();
        $recipe2->craftingTime = 3.4;
        $recipe2->isExpensive = true;

        $object = new EntityData();
        $object->type = 'abc';
        $object->name = 'def';
        $object->label = 'ghi';
        $object->recipes = [$recipe1, $recipe2];
        $object->numberOfRecipes = 42;
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
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

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
