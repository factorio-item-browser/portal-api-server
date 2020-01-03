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
    /**
     * Returns the object to test on.
     * @return object
     */
    protected function getObject(): object
    {
        $recipe = new RecipeData();
        $recipe->setCraftingTime(1.2)
               ->setIsExpensive(false);

        $expensiveRecipe = new RecipeData();
        $expensiveRecipe->setCraftingTime(3.4)
                        ->setIsExpensive(true);

        $object = new RecipeDetailsData();
        $object->setName('abc')
               ->setLabel('def')
               ->setDescription('ghi')
               ->setRecipe($recipe)
               ->setExpensiveRecipe($expensiveRecipe);
        return $object;
    }

    /**
     * Returns the data to test on.
     * @return array<mixed>
     */
    protected function getData(): array
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

    /**
     * Tests the serialization.
     */
    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
