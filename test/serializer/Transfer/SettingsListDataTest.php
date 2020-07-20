<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingsListData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the SettingsListData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingsListDataTest extends SerializerTestCase
{
    /**
     * Returns the object to test on.
     * @return object
     */
    protected function getObject(): object
    {
        $setting1 = new SettingMetaData();
        $setting1->setCombinationId('abc')
                 ->setName('def')
                 ->setStatus('yza');

        $setting2 = new SettingMetaData();
        $setting2->setCombinationId('ghi')
                 ->setName('jkl')
                 ->setStatus('bcd');

        $currentSetting = new SettingDetailsData();
        $currentSetting->setCombinationId('mno')
                       ->setName('pqr')
                       ->setStatus('efg')
                       ->setLocale('stu')
                       ->setRecipeMode('vwx');

        $object = new SettingsListData();
        $object->setSettings([$setting1, $setting2])
               ->setCurrentSetting($currentSetting);

        return $object;
    }

    /**
     * Returns the data to test on.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'settings' => [
                [
                    'combinationId' => 'abc',
                    'name' => 'def',
                    'status' => 'yza',
                ],
                [
                    'combinationId' => 'ghi',
                    'name' => 'jkl',
                    'status' => 'bcd',
                ],
            ],
            'currentSetting' => [
                'combinationId' => 'mno',
                'name' => 'pqr',
                'status' => 'efg',
                'locale' => 'stu',
                'recipeMode' => 'vwx',
                'mods' => [],
                'modIconsStyle' => [
                    'processedEntities' => [],
                    'style' => '',
                ],
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
