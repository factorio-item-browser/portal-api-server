<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the ModData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModDataTest extends SerializerTestCase
{
    private function getObject(): object
    {
        $object = new ModData();
        $object->name = 'abc';
        $object->label = 'def';
        $object->author = 'ghi';
        $object->version = 'jkl';

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
            'author' => 'ghi',
            'version' => 'jkl',
        ];
    }

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
