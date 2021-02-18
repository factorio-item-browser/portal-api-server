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
    private function getObject(): object
    {
        $result1 = new EntityData();
        $result1->type = 'mno';
        $result1->name = 'pqr';
        $result1->label = 'stu';
        $result1->numberOfRecipes = 12;

        $result2 = new EntityData();
        $result2->type = 'vwx';
        $result2->name = 'yza';
        $result2->label = 'bcd';
        $result2->numberOfRecipes = 34;

        $object = new ItemRecipesData();
        $object->type = 'abc';
        $object->name = 'def';
        $object->label = 'ghi';
        $object->description = 'jkl';
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

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
