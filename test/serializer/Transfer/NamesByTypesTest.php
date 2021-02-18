<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the NamesByTypes class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class NamesByTypesTest extends SerializerTestCase
{
    private function getObject(): object
    {
        $object = new NamesByTypes();
        $object->values = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno']
        ];
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
    {
        return [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno']
        ];
    }

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }

    /**
     * Tests the deserialization.
     */
    public function testDeserialize(): void
    {
        $this->assertDeserializedData($this->getObject(), $this->getData());
    }
}
