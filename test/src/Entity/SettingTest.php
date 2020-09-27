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
use Ramsey\Uuid\UuidInterface;

/**
 * The PHPUnit test of the Setting class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Entity\Setting
 */
class SettingTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     * @covers ::getSidebarEntities
     */
    public function testConstruct(): void
    {
        $entity = new Setting();

        $this->assertInstanceOf(ArrayCollection::class, $entity->getSidebarEntities());

        // Asserted through type hinting
        $entity->getLastUsageTime();
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
        $entity = new Setting();

        $this->assertSame($entity, $entity->setId($id));
        $this->assertSame($id, $entity->getId());
    }

    /**
     * Tests the setting and getting the user.
     * @covers ::getUser
     * @covers ::setUser
     */
    public function testSetAndGetUser(): void
    {
        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);
        $entity = new Setting();

        $this->assertSame($entity, $entity->setUser($user));
        $this->assertSame($user, $entity->getUser());
    }

    /**
     * Tests the setting and getting the combination.
     * @covers ::getCombination
     * @covers ::setCombination
     */
    public function testSetAndGetCombination(): void
    {
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $entity = new Setting();

        $this->assertSame($entity, $entity->setCombination($combination));
        $this->assertSame($combination, $entity->getCombination());
    }

    /**
     * Tests the setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $entity = new Setting();

        $this->assertSame($entity, $entity->setName($name));
        $this->assertSame($name, $entity->getName());
    }

    /**
     * Tests the setting and getting the locale.
     * @covers ::getLocale
     * @covers ::setLocale
     */
    public function testSetAndGetLocale(): void
    {
        $locale = 'abc';
        $entity = new Setting();

        $this->assertSame($entity, $entity->setLocale($locale));
        $this->assertSame($locale, $entity->getLocale());
    }

    /**
     * Tests the setting and getting the recipe mode.
     * @covers ::getRecipeMode
     * @covers ::setRecipeMode
     */
    public function testSetAndGetRecipeMode(): void
    {
        $recipeMode = 'abc';
        $entity = new Setting();

        $this->assertSame($entity, $entity->setRecipeMode($recipeMode));
        $this->assertSame($recipeMode, $entity->getRecipeMode());
    }

    /**
     * Tests the setting and getting the api authorization token.
     * @covers ::getApiAuthorizationToken
     * @covers ::setApiAuthorizationToken
     */
    public function testSetAndGetApiAuthorizationToken(): void
    {
        $apiAuthorizationToken = 'abc';
        $entity = new Setting();

        $this->assertSame($entity, $entity->setApiAuthorizationToken($apiAuthorizationToken));
        $this->assertSame($apiAuthorizationToken, $entity->getApiAuthorizationToken());
    }

    /**
     * Tests the setting and getting the last usage time.
     * @covers ::getLastUsageTime
     * @covers ::setLastUsageTime
     */
    public function testSetAndGetLastUsageTime(): void
    {
        $lastUsageTime = new DateTime('2038-01-19 03:14:07');
        $entity = new Setting();

        $this->assertSame($entity, $entity->setLastUsageTime($lastUsageTime));
        $this->assertSame($lastUsageTime, $entity->getLastUsageTime());
    }

    /**
     * Tests the setting and getting the has data.
     * @covers ::getHasData
     * @covers ::setHasData
     */
    public function testSetAndGetHasData(): void
    {
        $entity = new Setting();

        $this->assertSame($entity, $entity->setHasData(true));
        $this->assertTrue($entity->getHasData());
    }

    /**
     * Tests the setting and getting the is temporary.
     * @covers ::getIsTemporary
     * @covers ::setIsTemporary
     */
    public function testSetAndGetIsTemporary(): void
    {
        $entity = new Setting();

        $this->assertSame($entity, $entity->setIsTemporary(true));
        $this->assertTrue($entity->getIsTemporary());
    }
}
