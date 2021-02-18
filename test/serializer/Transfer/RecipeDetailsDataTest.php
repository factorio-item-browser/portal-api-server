<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeDetailsData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the RecipeDetailsData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeDetailsDataTest extends SerializerTestCase
{
    private function getObject(): object
    {
        $recipe = new RecipeData();
        $recipe->craftingTime = 1.2;
        $recipe->isExpensive = false;

        $expensiveRecipe = new RecipeData();
        $expensiveRecipe->craftingTime = 3.4;
        $expensiveRecipe->isExpensive = true;

        $object = new RecipeDetailsData();
        $object->name = 'abc';
        $object->label = 'def';
        $object->description = 'ghi';
        $object->recipe = $recipe;
        $object->expensiveRecipe = $expensiveRecipe;
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
    {
        return [
            'name' => 'abc',
            'label' => 'def',
            'description' => 'ghi',
            'recipe' => [
                    'craftingTime' => 1.2,
                    'ingredients' => [],
                    'products' => [],
                    'isExpensive' => false,
            ],
            'expensiveRecipe' => [
                'craftingTime' => 3.4,
                'ingredients' => [],
                'products' => [],
                'isExpensive' => true,
            ],
        ];
    }

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
