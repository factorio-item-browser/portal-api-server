<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use DateTime;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Transfer\InitData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
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
    /**
     * Returns the object to test on.
     * @return object
     * @throws Exception
     */
    protected function getObject(): object
    {
        $sidebarEntity1 = new SidebarEntityData();
        $sidebarEntity1->setType('abc')
                       ->setName('def')
                       ->setLabel('ghi')
                       ->setPinnedPosition(42)
                       ->setLastViewTime(new DateTime('2038-01-18 03:14:07.123'));

        $sidebarEntity2 = new SidebarEntityData();
        $sidebarEntity2->setType('jkl')
                       ->setName('mno')
                       ->setLabel('pqr')
                       ->setPinnedPosition(21)
                       ->setLastViewTime(new DateTime('2038-01-19 03:14:07.123'));

        $setting = new SettingMetaData();
        $setting->setCombinationId('stu')
                ->setName('vwx')
                ->setStatus('yza')
                ->setIsTemporary(true);

        $object = new InitData();
        $object->setSetting($setting)
               ->setLocale('efg')
               ->setSidebarEntities([$sidebarEntity1, $sidebarEntity2])
               ->setScriptVersion('hij');
        return $object;
    }

    /**
     * Returns the data to test on.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'setting' => [
                'combinationId' => 'stu',
                'name' => 'vwx',
                'status' => 'yza',
                'isTemporary' => true,
            ],
            'locale' => 'efg',
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
            'scriptVersion' => 'hij',
        ];
    }

    /**
     * Tests the serialization.
     * @throws Exception
     */
    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
