<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use DateTime;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingStatusData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the SettingStatusData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingStatusDataTest extends SerializerTestCase
{
    private function getObject(): object
    {
        $setting = new SettingDetailsData();
        $setting->combinationId = 'def';
        $setting->name = 'ghi';
        $setting->status = 'jkl';
        $setting->isTemporary = true;
        $setting->recipeMode = 'mno';
        $setting->locale = 'pqr';

        $object = new SettingStatusData();
        $object->status = 'abc';
        $object->exportTime = new DateTime('2038-01-19 03:14:07');
        $object->existingSetting = $setting;
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
    {
        return [
            'status' => 'abc',
            'exportTime' => '2038-01-19T03:14:07.000+00:00',
            'existingSetting' => [
                'combinationId' => 'def',
                'name' => 'ghi',
                'status' => 'jkl',
                'isTemporary' => true,
                'recipeMode' => 'mno',
                'locale' => 'pqr',
                'mods' => [],
                'modIconsStyle' => [
                    'processedEntities' => [],
                    'style' => '',
                ]
            ],
        ];
    }

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
