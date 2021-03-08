<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use DateTime;
use FactorioItemBrowser\PortalApi\Server\Transfer\InitData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the InitData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class InitDataTest extends SerializerTestCase
{
    private function getObject(): object
    {
        $sidebarEntity1 = new SidebarEntityData();
        $sidebarEntity1->type = 'abc';
        $sidebarEntity1->name = 'def';
        $sidebarEntity1->label = 'ghi';
        $sidebarEntity1->pinnedPosition = 42;
        $sidebarEntity1->lastViewTime = new DateTime('2038-01-18 03:14:07.123');

        $sidebarEntity2 = new SidebarEntityData();
        $sidebarEntity2->type = 'jkl';
        $sidebarEntity2->name = 'mno';
        $sidebarEntity2->label = 'pqr';
        $sidebarEntity2->pinnedPosition = 21;
        $sidebarEntity2->lastViewTime = new DateTime('2038-01-19 03:14:07.123');

        $setting = new SettingData();
        $setting->combinationId = 'stu';
        $setting->name = 'vwx';
        $setting->locale = 'yza';
        $setting->recipeMode = 'bcd';
        $setting->status = 'efg';
        $setting->isTemporary = true;

        $lastUsedSetting = new SettingData();
        $lastUsedSetting->combinationId = 'hij';
        $lastUsedSetting->name = 'klm';
        $lastUsedSetting->locale = 'nop';
        $lastUsedSetting->recipeMode = 'qrs';
        $lastUsedSetting->status = 'tuv';
        $lastUsedSetting->isTemporary = false;

        $object = new InitData();
        $object->setting = $setting;
        $object->lastUsedSetting = $lastUsedSetting;
        $object->sidebarEntities = [$sidebarEntity1, $sidebarEntity2];
        $object->scriptVersion = 'wxy';
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
    {
        return [
            'setting' => [
                'combinationId' => 'stu',
                'name' => 'vwx',
                'locale' => 'yza',
                'recipeMode' => 'bcd',
                'status' => 'efg',
                'isTemporary' => true,
            ],
            'lastUsedSetting' => [
                'combinationId' => 'hij',
                'name' => 'klm',
                'locale' => 'nop',
                'recipeMode' => 'qrs',
                'status' => 'tuv',
                'isTemporary' => false,
            ],
            'sidebarEntities' => [
                [
                    'type' => 'abc',
                    'name' => 'def',
                    'label' => 'ghi',
                    'pinnedPosition' => 42,
                    'lastViewTime' => '2038-01-18T03:14:07.123+00:00',
                ],
                [
                    'type' => 'jkl',
                    'name' => 'mno',
                    'label' => 'pqr',
                    'pinnedPosition' => 21,
                    'lastViewTime' => '2038-01-19T03:14:07.123+00:00',
                ],
            ],
            'scriptVersion' => 'wxy',
        ];
    }

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
