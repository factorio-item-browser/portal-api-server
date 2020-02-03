<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Repository;

use BluePsyduck\TestHelper\ReflectionTrait;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\PortalApi\Server\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use FactorioItemBrowser\PortalApi\Server\Repository\SidebarEntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the SettingRepository class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository
 */
class SettingRepositoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked entity manager.
     * @var EntityManagerInterface&MockObject
     */
    protected $entityManager;

    /**
     * The mocked sidebar entity repository.
     * @var SidebarEntityRepository&MockObject
     */
    protected $sidebarEntityRepository;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->sidebarEntityRepository = $this->createMock(SidebarEntityRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $repository = new SettingRepository($this->entityManager, $this->sidebarEntityRepository);

        $this->assertSame($this->entityManager, $this->extractProperty($repository, 'entityManager'));
        $this->assertSame(
            $this->sidebarEntityRepository,
            $this->extractProperty($repository, 'sidebarEntityRepository')
        );
    }

    /**
     * Tests the createSetting method.
     * @covers ::createSetting
     */
    public function testCreateSetting(): void
    {
        $name = 'abc';

        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);

        $expectedResult = new Setting();
        $expectedResult->setUser($user);

        $repository = new SettingRepository($this->entityManager, $this->sidebarEntityRepository);
        $result = $repository->createSetting($user, $name);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the createDefaultSetting method.
     * @covers ::createDefaultSetting
     */
    public function testCreateDefaultSetting(): void
    {
        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);

        $expectedResult = new Setting();
        $expectedResult->setUser($user)
                       ->setModNames([Constant::MOD_NAME_BASE])
                       ->setCombinationId(Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76'))
                       ->setRecipeMode(RecipeMode::HYBRID)
                       ->setLocale('en');

        $repository = new SettingRepository($this->entityManager, $this->sidebarEntityRepository);
        $result = $repository->createDefaultSetting($user);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the hydrateSidebarEntity method.
     * @throws Exception
     * @covers ::hydrateSidebarEntity
     */
    public function testHydrateSidebarEntity(): void
    {
        $lastViewTime = new DateTimeImmutable('2038-01-19 03:14:07');

        $source = new SidebarEntity();
        $source->setLabel('abc')
               ->setPinnedPosition(42)
               ->setLastViewTime($lastViewTime);

        $expectedDestination = new SidebarEntity();
        $expectedDestination->setType('foo')
                            ->setName('bar')
                            ->setLabel('abc')
                            ->setPinnedPosition(42)
                            ->setLastViewTime($lastViewTime);

        $destination = new SidebarEntity();
        $destination->setType('foo')
                    ->setName('bar');

        $repository = new SettingRepository($this->entityManager, $this->sidebarEntityRepository);
        $this->invokeMethod($repository, 'hydrateSidebarEntity', $source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
