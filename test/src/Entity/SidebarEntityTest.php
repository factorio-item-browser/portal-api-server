<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Entity;

use DateTime;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SidebarEntity class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity
 */
class SidebarEntityTest extends TestCase
{
    /**
     * Tests the setting and getting the setting.
     * @covers ::getSetting
     * @covers ::setSetting
     */
    public function testSetAndGetSetting(): void
    {
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        $entity = new SidebarEntity();

        $this->assertSame($entity, $entity->setSetting($setting));
        $this->assertSame($setting, $entity->getSetting());
    }

    /**
     * Tests the setting and getting the type.
     * @covers ::getType
     * @covers ::setType
     */
    public function testSetAndGetType(): void
    {
        $type = 'abc';
        $entity = new SidebarEntity();

        $this->assertSame($entity, $entity->setType($type));
        $this->assertSame($type, $entity->getType());
    }

    /**
     * Tests the setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $entity = new SidebarEntity();

        $this->assertSame($entity, $entity->setName($name));
        $this->assertSame($name, $entity->getName());
    }

    /**
     * Tests the setting and getting the label.
     * @covers ::getLabel
     * @covers ::setLabel
     */
    public function testSetAndGetLabel(): void
    {
        $label = 'abc';
        $entity = new SidebarEntity();

        $this->assertSame($entity, $entity->setLabel($label));
        $this->assertSame($label, $entity->getLabel());
    }

    /**
     * Tests the setting and getting the pinned position.
     * @covers ::getPinnedPosition
     * @covers ::setPinnedPosition
     */
    public function testSetAndGetPinnedPosition(): void
    {
        $pinnedPosition = 42;
        $entity = new SidebarEntity();

        $this->assertSame($entity, $entity->setPinnedPosition($pinnedPosition));
        $this->assertSame($pinnedPosition, $entity->getPinnedPosition());
    }

    /**
     * Tests the setting and getting the last view time.
     * @covers ::getLastViewTime
     * @covers ::setLastViewTime
     */
    public function testSetAndGetLastViewTime(): void
    {
        $lastViewTime = new DateTime('2038-01-19 03:14:07');
        $entity = new SidebarEntity();

        $this->assertSame($entity, $entity->setLastViewTime($lastViewTime));
        $this->assertSame($lastViewTime, $entity->getLastViewTime());
    }
}
