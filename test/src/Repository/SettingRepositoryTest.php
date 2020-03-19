<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Repository;

use BluePsyduck\TestHelper\ReflectionTrait;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Repository\CombinationRepository;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
     * The mocked combination repository.
     * @var CombinationRepository&MockObject
     */
    protected $combinationRepository;

    /**
     * The mocked entity manager.
     * @var EntityManagerInterface&MockObject
     */
    protected $entityManager;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->combinationRepository = $this->createMock(CombinationRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $repository = new SettingRepository(
            $this->combinationRepository,
            $this->entityManager
        );

        $this->assertSame($this->combinationRepository, $this->extractProperty($repository, 'combinationRepository'));
        $this->assertSame($this->entityManager, $this->extractProperty($repository, 'entityManager'));
    }

    /**
     * Tests the createSetting method.
     * @covers ::createSetting
     * @throws Exception
     */
    public function testCreateSetting(): void
    {
        $name = 'abc';

        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        $repository = new SettingRepository(
            $this->combinationRepository,
            $this->entityManager
        );
        $result = $repository->createSetting($user, $combination, $name);

        $result->getId(); // Asserted by type-hint.
        $this->assertSame($user, $result->getUser());
        $this->assertSame($combination, $result->getCombination());
        $this->assertSame($name, $result->getName());
    }

    /**
     * Tests the createDefaultSetting method.
     * @covers ::createDefaultSetting
     * @throws Exception
     */
    public function testCreateDefaultSetting(): void
    {
        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);
        /* @var Combination&MockObject $$defaultCombination */
        $defaultCombination = $this->createMock(Combination::class);

        $this->combinationRepository->expects($this->once())
                                    ->method('getDefaultCombination')
                                    ->willReturn($defaultCombination);

        $repository = new SettingRepository(
            $this->combinationRepository,
            $this->entityManager
        );
        $result = $repository->createDefaultSetting($user);

        $result->getId(); // Asserted by type-hint.
        $this->assertSame($user, $result->getUser());
        $this->assertSame($defaultCombination, $result->getCombination());
        $this->assertSame('Vanilla', $result->getName());
        $this->assertSame(RecipeMode::HYBRID, $result->getRecipeMode());
        $this->assertSame('en', $result->getLocale());
    }
}
