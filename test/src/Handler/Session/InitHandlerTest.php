<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Session;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Handler\Session\InitHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SessionInitData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

/**
 * The PHPUnit test of the InitHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Session\InitHandler
 */
class InitHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked current setting.
     * @var Setting&MockObject
     */
    protected $currentSetting;

    /**
     * The mocked mapper manager.
     * @var MapperManagerInterface&MockObject
     */
    protected $mapperManager;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->currentSetting = $this->createMock(Setting::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new InitHandler($this->currentSetting, $this->mapperManager);

        $this->assertSame($this->currentSetting, $this->extractProperty($handler, 'currentSetting'));
        $this->assertSame($this->mapperManager, $this->extractProperty($handler, 'mapperManager'));
    }

    /**
     * Tests the handle method.
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $sidebarEntities = [
            $this->createMock(SidebarEntityData::class),
            $this->createMock(SidebarEntityData::class),
        ];

        $expectedTransfer = new SessionInitData();
        $expectedTransfer->setSidebarEntities($sidebarEntities);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);

        /* @var InitHandler&MockObject $handler */
        $handler = $this->getMockBuilder(InitHandler::class)
                        ->onlyMethods(['getCurrentSidebarEntities'])
                        ->setConstructorArgs([$this->currentSetting, $this->mapperManager])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getCurrentSidebarEntities')
                ->willReturn($sidebarEntities);

        /* @var TransferResponse $result */
        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }

    /**
     * Tests the getCurrentSidebarEntities method.
     * @throws ReflectionException
     * @covers ::getCurrentSidebarEntities
     */
    public function testGetCurrentSidebarEntities(): void
    {
        /* @var SidebarEntity&MockObject $sidebarEntity1 */
        $sidebarEntity1 = $this->createMock(SidebarEntity::class);
        /* @var SidebarEntity&MockObject $sidebarEntity2 */
        $sidebarEntity2 = $this->createMock(SidebarEntity::class);
        /* @var SidebarEntityData&MockObject $sidebarEntityData1 */
        $sidebarEntityData1 = $this->createMock(SidebarEntityData::class);
        /* @var SidebarEntityData&MockObject $sidebarEntityData2 */
        $sidebarEntityData2 = $this->createMock(SidebarEntityData::class);

        $expectedResult = [$sidebarEntityData1, $sidebarEntityData2];

        $this->currentSetting->expects($this->once())
                             ->method('getSidebarEntities')
                             ->willReturn(new ArrayCollection([$sidebarEntity1, $sidebarEntity2]));

        /* @var InitHandler&MockObject $handler */
        $handler = $this->getMockBuilder(InitHandler::class)
                        ->onlyMethods(['mapSidebarEntity'])
                        ->setConstructorArgs([$this->currentSetting, $this->mapperManager])
                        ->getMock();
        $handler->expects($this->exactly(2))
                ->method('mapSidebarEntity')
                ->withConsecutive(
                    [$this->identicalTo($sidebarEntity1)],
                    [$this->identicalTo($sidebarEntity2)]
                )
                ->willReturnOnConsecutiveCalls(
                    $sidebarEntityData1,
                    $sidebarEntityData2
                );

        $result = $this->invokeMethod($handler, 'getCurrentSidebarEntities');

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the mapSidebarEntity method.
     * @throws ReflectionException
     * @covers ::mapSidebarEntity
     */
    public function testMapSidebarEntity(): void
    {
        /* @var SidebarEntity&MockObject $sidebarEntity */
        $sidebarEntity = $this->createMock(SidebarEntity::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($sidebarEntity), $this->isInstanceOf(SidebarEntityData::class));

        $handler = new InitHandler($this->currentSetting, $this->mapperManager);

        $this->invokeMethod($handler, 'mapSidebarEntity', $sidebarEntity);
    }

    /**
     * Tests the mapSidebarEntity method.
     * @throws ReflectionException
     * @covers ::mapSidebarEntity
     */
    public function testMapSidebarEntityWithException(): void
    {
        /* @var SidebarEntity&MockObject $sidebarEntity */
        $sidebarEntity = $this->createMock(SidebarEntity::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($sidebarEntity), $this->isInstanceOf(SidebarEntityData::class))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        $handler = new InitHandler($this->currentSetting, $this->mapperManager);

        $this->invokeMethod($handler, 'mapSidebarEntity', $sidebarEntity);
    }
}
