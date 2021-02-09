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
    private function getObject(): object
    {
        $setting1 = new SettingMetaData();
        $setting1->combinationId = 'abc';
        $setting1->name = 'def';
        $setting1->status = 'yza';

        $setting2 = new SettingMetaData();
        $setting2->combinationId = 'ghi';
        $setting2->name = 'jkl';
        $setting2->status = 'bcd';
        $setting2->isTemporary = true;

        $currentSetting = new SettingDetailsData();
        $currentSetting->combinationId = 'mno';
        $currentSetting->name = 'pqr';
        $currentSetting->status = 'efg';
        $currentSetting->locale = 'stu';
        $currentSetting->recipeMode = 'vwx';

        $object = new SettingsListData();
        $object->settings = [$setting1, $setting2];
        $object->currentSetting = $currentSetting;
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
    {
        return [
            'settings' => [
                [
                    'combinationId' => 'abc',
                    'name' => 'def',
                    'status' => 'yza',
                    'isTemporary' => false,
                ],
                [
                    'combinationId' => 'ghi',
                    'name' => 'jkl',
                    'status' => 'bcd',
                    'isTemporary' => true,
                ],
            ],
            'currentSetting' => [
                'combinationId' => 'mno',
                'name' => 'pqr',
                'status' => 'efg',
                'isTemporary' => false,
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

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
