<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SearchResultsData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the SearchResultsData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchResultsDataTest extends SerializerTestCase
{
    private function getObject(): object
    {
        $result1 = new EntityData();
        $result1->type = 'def';
        $result1->name = 'ghi';
        $result1->label = 'jkl';
        $result1->numberOfRecipes = 12;

        $result2 = new EntityData();
        $result2->type = 'mno';
        $result2->name = 'pqr';
        $result2->label = 'stu';
        $result2->numberOfRecipes = 34;

        $object = new SearchResultsData();
        $object->query = 'abc';
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
            'query' => 'abc',
            'results' => [
                [
                    'type' => 'def',
                    'name' => 'ghi',
                    'label' => 'jkl',
                    'recipes' => [],
                    'numberOfRecipes' => 12,
                ],
                [
                    'type' => 'mno',
                    'name' => 'pqr',
                    'label' => 'stu',
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
