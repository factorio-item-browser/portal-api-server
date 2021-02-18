<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the IconsStyleData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconsStyleDataTest extends SerializerTestCase
{
    private function getObject(): object
    {
        $object = new IconsStyleData();
        $object->processedEntities->values = [
            'abc' => ['def', 'jkl'],
            'mno' => ['pqr'],
        ];
        $object->style = 'stu';
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
    {
        return [
            'processedEntities' => [
                'abc' => ['def', 'jkl'],
                'mno' => ['pqr'],
            ],
            'style' => 'stu',
        ];
    }

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
