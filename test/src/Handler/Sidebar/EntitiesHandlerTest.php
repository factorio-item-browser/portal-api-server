<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Sidebar;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Handler\Sidebar\EntitiesHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use Laminas\Diactoros\Response\EmptyResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The PHPUnit test of the EntitiesHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Sidebar\EntitiesHandler
 */
class EntitiesHandlerTest extends TestCase
{
    /** @var Setting&MockObject */
    private Setting $currentSetting;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var SidebarEntitiesHelper&MockObject */
    private SidebarEntitiesHelper $sidebarEntitiesHelper;

    protected function setUp(): void
    {
        $this->currentSetting = $this->createMock(Setting::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->sidebarEntitiesHelper = $this->createMock(SidebarEntitiesHelper::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return EntitiesHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): EntitiesHandler
    {
        return $this->getMockBuilder(EntitiesHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->currentSetting,
                        $this->mapperManager,
                        $this->sidebarEntitiesHelper,
                    ])
                    ->getMock();
    }

    public function testHandle(): void
    {
        $entityData1 = $this->createMock(SidebarEntityData::class);
        $entityData2 = $this->createMock(SidebarEntityData::class);
        $entity1 = $this->createMock(SidebarEntity::class);
        $entity2 = $this->createMock(SidebarEntity::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn([$entityData1, $entityData2]);

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($entityData1), $this->isInstanceOf(SidebarEntity::class)],
                                [$this->identicalTo($entityData2), $this->isInstanceOf(SidebarEntity::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $entity1,
                                $entity2,
                            );

        $this->sidebarEntitiesHelper->expects($this->once())
                                    ->method('replaceEntities')
                                    ->with(
                                        $this->identicalTo($this->currentSetting),
                                        $this->identicalTo([$entity1, $entity2])
                                    );

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(EmptyResponse::class, $result);
    }
}
