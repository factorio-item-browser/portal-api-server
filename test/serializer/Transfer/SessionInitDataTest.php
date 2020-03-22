<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use DateTime;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Transfer\SessionInitData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the SessionInitData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SessionInitDataTest extends SerializerTestCase
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

        $object = new SessionInitData();
        $object->setSettingName('stu')
               ->setSettingHash('vwx')
               ->setLocale('yza')
               ->setSidebarEntities([$sidebarEntity1, $sidebarEntity2]);
        return $object;
    }

    /**
     * Returns the data to test on.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'settingName' => 'stu',
            'settingHash' => 'vwx',
            'locale' => 'yza',
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
