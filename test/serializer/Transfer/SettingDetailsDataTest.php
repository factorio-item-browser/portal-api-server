<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
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

        $modIconsStyle = new IconsStyleData();
        $modIconsStyle->setStyle('klm');

        $object = new SettingDetailsData();
        $object->setCombinationId('yza')
               ->setName('bcd')
               ->setStatus('klm')
               ->setLocale('efg')
               ->setRecipeMode('hij')
               ->setMods([$mod1, $mod2])
               ->setModIconsStyle($modIconsStyle);

        return $object;
    }

    /**
     * Returns the data to test on.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'combinationId' => 'yza',
            'name' => 'bcd',
            'status' => 'klm',
            'locale' => 'efg',
            'recipeMode' => 'hij',
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
            'modIconsStyle' => [
                'processedEntities' => [],
                'style' => 'klm',
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
