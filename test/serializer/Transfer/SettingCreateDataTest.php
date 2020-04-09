<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\SettingCreateData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the SettingCreateData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingCreateDataTest extends SerializerTestCase
{
    /**
     * Returns the object to test on.
     * @return object
     */
    protected function getObject(): object
    {
        $object = new SettingCreateData();
        $object->setName('abc')
               ->setLocale('def')
               ->setRecipeMode('ghi')
               ->setModNames(['jkl', 'mno']);

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
            'locale' => 'def',
            'recipeMode' => 'ghi',
            'modNames' => ['jkl', 'mno'],
        ];
    }

    /**
     * Tests the serialization.
     */
    public function testSerialize(): void
    {
        $this->assertDeserializedData($this->getObject(), $this->getData());
    }
}
