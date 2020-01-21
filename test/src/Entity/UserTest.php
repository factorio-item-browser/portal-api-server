<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * The PHPUnit test of the User class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Entity\User
 */
class UserTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     * @covers ::getSettings
     */
    public function testConstruct(): void
    {
        $entity = new User();

        $this->assertInstanceOf(ArrayCollection::class, $entity->getSettings());
    }

    /**
     * Tests the setting and getting the id.
     * @covers ::getId
     * @covers ::setId
     */
    public function testSetAndGetId(): void
    {
        /* @var UuidInterface&MockObject $id */
        $id = $this->createMock(UuidInterface::class);
        $entity = new User();

        $this->assertSame($entity, $entity->setId($id));
        $this->assertSame($id, $entity->getId());
    }

    /**
     * Tests the setting and getting the locale.
     * @covers ::getLocale
     * @covers ::setLocale
     */
    public function testSetAndGetLocale(): void
    {
        $locale = 'abc';
        $entity = new User();

        $this->assertSame($entity, $entity->setLocale($locale));
        $this->assertSame($locale, $entity->getLocale());
    }

    /**
     * Tests the setting and getting the last visit time.
     * @covers ::getLastVisitTime
     * @covers ::setLastVisitTime
     */
    public function testSetAndGetLastVisitTime(): void
    {
        $lastVisitTime = new DateTime('2038-01-19 03:14:07');
        $entity = new User();

        $this->assertSame($entity, $entity->setLastVisitTime($lastVisitTime));
        $this->assertSame($lastVisitTime, $entity->getLastVisitTime());
    }

    /**
     * Tests the setting and getting the current setting.
     * @covers ::getCurrentSetting
     * @covers ::setCurrentSetting
     */
    public function testSetAndGetCurrentSetting(): void
    {
        /* @var Setting&MockObject $currentSetting */
        $currentSetting = $this->createMock(Setting::class);
        $entity = new User();
    
        $this->assertSame($entity, $entity->setCurrentSetting($currentSetting));
        $this->assertSame($currentSetting, $entity->getCurrentSetting());
    }
}
