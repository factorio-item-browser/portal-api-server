<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Sidebar;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Sidebar\EntitiesHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use JMS\Serializer\SerializerInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionException;

/**
 * The PHPUnit test of the EntitiesHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Sidebar\EntitiesHandler
 */
class EntitiesHandlerTest extends TestCase
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
     * The mocked serializer.
     * @var SerializerInterface&MockObject
     */
    protected $serializer;

    /**
     * The mocked sidebar entities helper.
     * @var SidebarEntitiesHelper&MockObject
     */
    protected $sidebarEntitiesHelper;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->currentSetting = $this->createMock(Setting::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->sidebarEntitiesHelper = $this->createMock(SidebarEntitiesHelper::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new EntitiesHandler(
            $this->currentSetting,
            $this->mapperManager,
            $this->serializer,
            $this->sidebarEntitiesHelper
        );

        $this->assertSame($this->currentSetting, $this->extractProperty($handler, 'currentSetting'));
        $this->assertSame($this->mapperManager, $this->extractProperty($handler, 'mapperManager'));
        $this->assertSame($this->serializer, $this->extractProperty($handler, 'serializer'));
        $this->assertSame($this->sidebarEntitiesHelper, $this->extractProperty($handler, 'sidebarEntitiesHelper'));
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $newEntities = [
            $this->createMock(SidebarEntity::class),
            $this->createMock(SidebarEntity::class),
        ];

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);

        $this->sidebarEntitiesHelper->expects($this->once())
                                    ->method('replaceEntities')
                                    ->with($this->identicalTo($this->currentSetting), $this->identicalTo($newEntities));

        /* @var EntitiesHandler&MockObject $handler */
        $handler = $this->getMockBuilder(EntitiesHandler::class)
                        ->onlyMethods(['parseRequestBody'])
                        ->setConstructorArgs([
                            $this->currentSetting,
                            $this->mapperManager,
                            $this->serializer,
                            $this->sidebarEntitiesHelper,
                        ])
                        ->getMock();
        $handler->expects($this->once())
                ->method('parseRequestBody')
                ->with($this->identicalTo($request))
                ->willReturn($newEntities);

        $result = $handler->handle($request);

        $this->assertInstanceOf(EmptyResponse::class, $result);
    }

    /**
     * Tests the parseRequestBody method.
     * @throws ReflectionException
     * @covers ::parseRequestBody
     */
    public function testParseRequestBody(): void
    {
        $requestBody = 'abc';

        /* @var SidebarEntityData&MockObject $entityData1 */
        $entityData1 = $this->createMock(SidebarEntityData::class);
        /* @var SidebarEntityData&MockObject $entityData2 */
        $entityData2 = $this->createMock(SidebarEntityData::class);

        /* @var SidebarEntity&MockObject $entity1 */
        $entity1 = $this->createMock(SidebarEntity::class);
        /* @var SidebarEntity&MockObject $entity2 */
        $entity2 = $this->createMock(SidebarEntity::class);

        $deserializedRequestBody = [$entityData1, $entityData2];
        $expectedResult = [$entity1, $entity2];

        /* @var StreamInterface&MockObject $body */
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
             ->method('getContents')
             ->willReturn($requestBody);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getBody')
                ->willReturn($body);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($requestBody),
                             $this->identicalTo(sprintf('array<%s>', SidebarEntityData::class)),
                             $this->identicalTo('json')
                         )
                         ->willReturn($deserializedRequestBody);

        /* @var EntitiesHandler&MockObject $handler */
        $handler = $this->getMockBuilder(EntitiesHandler::class)
                        ->onlyMethods(['mapEntity'])
                        ->setConstructorArgs([
                            $this->currentSetting,
                            $this->mapperManager,
                            $this->serializer,
                            $this->sidebarEntitiesHelper,
                        ])
                        ->getMock();
        $handler->expects($this->exactly(2))
                ->method('mapEntity')
                ->withConsecutive(
                    [$this->identicalTo($entityData1)],
                    [$this->identicalTo($entityData2)]
                )
                ->willReturnOnConsecutiveCalls(
                    $entity1,
                    $entity2
                );

        $result = $this->invokeMethod($handler, 'parseRequestBody', $request);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the parseRequestBody method.
     * @throws ReflectionException
     * @covers ::parseRequestBody
     */
    public function testParseRequestBodyWithException(): void
    {
        $requestBody = 'abc';

        /* @var StreamInterface&MockObject $body */
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
             ->method('getContents')
             ->willReturn($requestBody);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getBody')
                ->willReturn($body);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($requestBody),
                             $this->identicalTo(sprintf('array<%s>', SidebarEntityData::class)),
                             $this->identicalTo('json')
                         )
                         ->willThrowException($this->createMock(Exception::class));

        $this->expectException(InvalidRequestException::class);

        /* @var EntitiesHandler&MockObject $handler */
        $handler = $this->getMockBuilder(EntitiesHandler::class)
                        ->onlyMethods(['mapEntity'])
                        ->setConstructorArgs([
                            $this->currentSetting,
                            $this->mapperManager,
                            $this->serializer,
                            $this->sidebarEntitiesHelper,
                        ])
                        ->getMock();
        $handler->expects($this->never())
                ->method('mapEntity');

        $this->invokeMethod($handler, 'parseRequestBody', $request);
    }

    /**
     * Tests the mapEntity method.
     * @throws ReflectionException
     * @covers ::mapEntity
     */
    public function testMapEntity(): void
    {
        /* @var SidebarEntityData&MockObject $sidebarEntityData */
        $sidebarEntityData = $this->createMock(SidebarEntityData::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($sidebarEntityData), $this->isInstanceOf(SidebarEntity::class));

        $handler = new EntitiesHandler(
            $this->currentSetting,
            $this->mapperManager,
            $this->serializer,
            $this->sidebarEntitiesHelper
        );

        $this->invokeMethod($handler, 'mapEntity', $sidebarEntityData);
    }
}
