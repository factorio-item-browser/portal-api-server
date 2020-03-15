<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the SettingDetailsData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingDetailsDataTest extends SerializerTestCase
{
    /**
     * Returns the object to test on.
     * @return object
     */
    protected function getObject(): object
    {
        $mod1 = new ModData();
        $mod1->setName('abc')
             ->setLabel('def')
             ->setAuthor('ghi')
             ->setVersion('jkl');

        $mod2 = new ModData();
        $mod2->setName('mno')
             ->setLabel('pqr')
             ->setAuthor('stu')
             ->setVersion('vwx');

        $object = new SettingDetailsData();
        $object->setId('yza')
               ->setName('bcd')
               ->setMods([$mod1, $mod2])
               ->setLocale('efg')
               ->setRecipeMode('hij');

        return $object;
    }

    /**
     * Returns the data to test on.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'id' => 'yza',
            'name' => 'bcd',
            'mods' => [
                [
                    'name' => 'abc',
                    'label' => 'def',
                    'author' => 'ghi',
                    'version' => 'jkl',
                ],
                [
                    'name' => 'mno',
                    'label' => 'pqr',
                    'author' => 'stu',
                    'version' => 'vwx',
                ],
            ],
            'locale' => 'efg',
            'recipeMode' => 'hij',
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
