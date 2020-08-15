<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
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
     * Tests the getSettingByCombinationId method.
     * @covers ::getSettingByCombinationId
     */
    public function testGetSettingByCombinationId(): void
    {
        $combinationId1 = '17060d93-bc42-4c04-abbf-f7c7ed7b7ad3';
        $combination1 = new Combination();
        $combination1->setId(Uuid::fromString($combinationId1));
        $setting1 = new Setting();
        $setting1->setCombination($combination1);

        $combinationId2 = '259f8986-37b0-4e07-a5c9-235e066fb232';
        $combination2 = new Combination();
        $combination2->setId(Uuid::fromString($combinationId2));
        $setting2 = new Setting();
        $setting2->setCombination($combination2);

        $combinationId = Uuid::fromString($combinationId2);
        $expectedResult = $setting2;

        $user = new User();
        $user->getSettings()->add($setting1);
        $user->getSettings()->add($setting2);

        $result = $user->getSettingByCombinationId($combinationId);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getSettingByCombinationId method.
     * @covers ::getSettingByCombinationId
     */
    public function testGetSettingByCombinationIdWithoutMatch(): void
    {
        $combinationId1 = '17060d93-bc42-4c04-abbf-f7c7ed7b7ad3';
        $combination1 = new Combination();
        $combination1->setId(Uuid::fromString($combinationId1));
        $setting1 = new Setting();
        $setting1->setCombination($combination1);

        $combinationId2 = '259f8986-37b0-4e07-a5c9-235e066fb232';
        $combination2 = new Combination();
        $combination2->setId(Uuid::fromString($combinationId2));
        $setting2 = new Setting();
        $setting2->setCombination($combination2);

        $combinationId = Uuid::fromString('f123ae9b-e7fd-4354-ba54-36169cf3db35');

        $user = new User();
        $user->getSettings()->add($setting1);
        $user->getSettings()->add($setting2);

        $result = $user->getSettingByCombinationId($combinationId);

        $this->assertNull($result);
    }

    /**
     * Tests the getLastUsedSetting method.
     * @covers ::getLastUsedSetting
     */
    public function testGetLastUsedSetting(): void
    {
        $setting1 = new Setting();
        $setting1->setLastUsageTime(new DateTime('2000-01-01'))
                 ->setIsTemporary(false);

        $setting2 = new Setting();
        $setting2->setLastUsageTime(new DateTime('2010-01-01'))
                 ->setIsTemporary(true);

        $setting3 = new Setting();
        $setting3->setLastUsageTime(new DateTime('2005-01-01'))
                 ->setIsTemporary(false);

        $setting4 = new Setting();
        $setting4->setLastUsageTime(new DateTime('2001-01-01'))
                 ->setIsTemporary(true);

        $expectedResult = $setting3;

        $user = new User();
        $user->getSettings()->add($setting1);
        $user->getSettings()->add($setting2);
        $user->getSettings()->add($setting3);
        $user->getSettings()->add($setting4);

        $result = $user->getLastUsedSetting();

        $this->assertSame($expectedResult, $result);
    }
}
