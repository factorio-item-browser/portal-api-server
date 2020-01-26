<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Sidebar;

use BluePsyduck\TestHelper\ReflectionTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Sidebar\EntitiesHandler;
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
     * The mocked serializer.
     * @var SerializerInterface&MockObject
     */
    protected $serializer;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->currentSetting = $this->createMock(Setting::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new EntitiesHandler($this->currentSetting, $this->serializer);

        $this->assertSame($this->currentSetting, $this->extractProperty($handler, 'currentSetting'));
        $this->assertSame($this->serializer, $this->extractProperty($handler, 'serializer'));
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $existingEntities = [
            $this->createMock(SidebarEntity::class),
            $this->createMock(SidebarEntity::class),
        ];
        $requestEntities = [
            $this->createMock(SidebarEntityData::class),
            $this->createMock(SidebarEntityData::class),
        ];
        $newEntities = [
            $this->createMock(SidebarEntity::class),
            $this->createMock(SidebarEntity::class),
        ];

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);

        /* @var EntitiesHandler&MockObject $handler */
        $handler = $this->getMockBuilder(EntitiesHandler::class)
                        ->onlyMethods([
                            'getExistingEntities',
                            'getEntitiesFromRequest',
                            'buildNewEntities',
                            'applyNewEntities',
                        ])
                        ->setConstructorArgs([$this->currentSetting, $this->serializer])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getExistingEntities')
                ->willReturn($existingEntities);
        $handler->expects($this->once())
                ->method('getEntitiesFromRequest')
                ->with($this->identicalTo($request))
                ->willReturn($requestEntities);
        $handler->expects($this->once())
                ->method('buildNewEntities')
                ->with($this->identicalTo($existingEntities), $this->identicalTo($requestEntities))
                ->willReturn($newEntities);
        $handler->expects($this->once())
                ->method('applyNewEntities')
                ->with($this->identicalTo($newEntities));

        $result = $handler->handle($request);

        $this->assertInstanceOf(EmptyResponse::class, $result);
    }
    
    /**
     * Tests the getExistingEntities method.
     * @throws ReflectionException
     * @covers ::getExistingEntities
     */
    public function testGetExistingEntities(): void
    {
        $entity1 = new SidebarEntity();
        $entity1->setType('abc')
                ->setName('def');
        
        $entity2 = new SidebarEntity();
        $entity2->setType('ghi')
                ->setName('jkl');

        $expectedResult = [
            'abc|def' => $entity1,
            'ghi|jkl' => $entity2,
        ];
        
        $this->currentSetting->expects($this->once())
                             ->method('getSidebarEntities')
                             ->willReturn(new ArrayCollection([$entity1, $entity2]));

        $handler = new EntitiesHandler($this->currentSetting, $this->serializer);
        $result = $this->invokeMethod($handler, 'getExistingEntities');
        
        $this->assertSame($expectedResult, $result);
    }
    
    /**
     * Tests the getEntitiesFromRequest method.
     * @throws ReflectionException
     * @covers ::getEntitiesFromRequest
     */
    public function testGetEntitiesFromRequest(): void
    {
        $entity1 = new SidebarEntityData();
        $entity1->setType('abc')
                ->setName('def');
        
        $entity2 = new SidebarEntityData();
        $entity2->setType('ghi')
                ->setName('jkl');

        $expectedResult = [
            'abc|def' => $entity1,
            'ghi|jkl' => $entity2,
        ];
        
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);

        /* @var EntitiesHandler&MockObject $handler */
        $handler = $this->getMockBuilder(EntitiesHandler::class)
                        ->onlyMethods(['parseRequestBody'])
                        ->setConstructorArgs([$this->currentSetting, $this->serializer])
                        ->getMock();
        $handler->expects($this->once())
                ->method('parseRequestBody')
                ->with($this->identicalTo($request))
                ->willReturn([$entity1, $entity2]);
        
        $result = $this->invokeMethod($handler, 'getEntitiesFromRequest', $request);
        
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the parseRequestBody method.
     * @throws ReflectionException
     * @covers ::parseRequestBody
     */
    public function testParseRequestBody(): void
    {
        $requestBody = 'abc';

        $deserializedRequestBody = [
            $this->createMock(SidebarEntityData::class),
            $this->createMock(SidebarEntityData::class),
        ];

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

        $handler = new EntitiesHandler($this->currentSetting, $this->serializer);
        $result = $this->invokeMethod($handler, 'parseRequestBody', $request);

        $this->assertSame($deserializedRequestBody, $result);
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

        $handler = new EntitiesHandler($this->currentSetting, $this->serializer);
        $this->invokeMethod($handler, 'parseRequestBody', $request);
    }

    /**
     * Tests the buildNewEntities method.
     * @throws ReflectionException
     * @covers ::buildNewEntities
     */
    public function testBuildNewEntities(): void
    {
        /* @var SidebarEntity&MockObject $existingEntity1 */
        $existingEntity1 = $this->createMock(SidebarEntity::class);
        /* @var SidebarEntity&MockObject $existingEntity2 */
        $existingEntity2 = $this->createMock(SidebarEntity::class);

        $existingEntities = [
            'abc|def' => $existingEntity1,
            'ghi|jkl' => $existingEntity2,
        ];

        /* @var SidebarEntityData&MockObject $requestEntity1 */
        $requestEntity1 = $this->createMock(SidebarEntityData::class);
        /* @var SidebarEntityData&MockObject $requestEntity2 */
        $requestEntity2 = $this->createMock(SidebarEntityData::class);
        $requestEntity2->expects($this->once())
                       ->method('getType')
                       ->willReturn('mno');
        $requestEntity2->expects($this->once())
                       ->method('getName')
                       ->willReturn('pqr');

        $requestEntities = [
            'abc|def' => $requestEntity1,
            'mno|pqr' => $requestEntity2,
        ];

        /* @var SidebarEntity&MockObject $newEntity */
        $newEntity = $this->createMock(SidebarEntity::class);

        $expectedResult = [
            'abc|def' => $existingEntity1,
            'mno|pqr' => $newEntity,
        ];

        /* @var EntitiesHandler&MockObject $handler */
        $handler = $this->getMockBuilder(EntitiesHandler::class)
                        ->onlyMethods(['createEntity', 'hydrateEntity'])
                        ->setConstructorArgs([$this->currentSetting, $this->serializer])
                        ->getMock();
        $handler->expects($this->once())
                ->method('createEntity')
                ->with($this->identicalTo('mno'), $this->identicalTo('pqr'))
                ->willReturn($newEntity);
        $handler->expects($this->exactly(2))
                ->method('hydrateEntity')
                ->withConsecutive(
                    [$this->identicalTo($existingEntity1), $this->identicalTo($requestEntity1)],
                    [$this->identicalTo($newEntity), $this->identicalTo($requestEntity2)]
                );

        $result = $this->invokeMethod($handler, 'buildNewEntities', $existingEntities, $requestEntities);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the createEntity method.
     * @throws ReflectionException
     * @covers ::createEntity
     */
    public function testCreateEntity(): void
    {
        $type = 'abc';
        $name = 'def';

        $expectedResult = new SidebarEntity();
        $expectedResult->setSetting($this->currentSetting)
                       ->setType('abc')
                       ->setName('def');

        $handler = new EntitiesHandler($this->currentSetting, $this->serializer);
        $result = $this->invokeMethod($handler, 'createEntity', $type, $name);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the hydrateEntity method.
     * @throws Exception
     * @covers ::hydrateEntity
     */
    public function testHydrateEntity(): void
    {
        $lastViewTime = new DateTimeImmutable('2038-01-19 03:14:07');

        $requestEntity = new SidebarEntityData();
        $requestEntity->setLabel('abc')
                      ->setPinnedPosition(42)
                      ->setLastViewTime($lastViewTime);

        $expectedNewEntity = new SidebarEntity();
        $expectedNewEntity->setType('foo')
                          ->setName('bar')
                          ->setLabel('abc')
                          ->setPinnedPosition(42)
                          ->setLastViewTime($lastViewTime);

        $newEntity = new SidebarEntity();
        $newEntity->setType('foo')
                  ->setName('bar');

        $handler = new EntitiesHandler($this->currentSetting, $this->serializer);
        $this->invokeMethod($handler, 'hydrateEntity', $newEntity, $requestEntity);

        $this->assertEquals($expectedNewEntity, $newEntity);
    }

    /**
     * Tests the applyNewEntities method.
     * @throws ReflectionException
     * @covers ::applyNewEntities
     */
    public function testApplyNewEntities(): void
    {
        /* @var SidebarEntity&MockObject $entity1 */
        $entity1 = $this->createMock(SidebarEntity::class);
        /* @var SidebarEntity&MockObject $entity2 */
        $entity2 = $this->createMock(SidebarEntity::class);

        /* @var Collection&MockObject $collection */
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
                   ->method('clear');
        $collection->expects($this->exactly(2))
                   ->method('add')
                   ->withConsecutive(
                       [$this->identicalTo($entity1)],
                       [$this->identicalTo($entity2)]
                   );

        $this->currentSetting->expects($this->once())
                             ->method('getSidebarEntities')
                             ->willReturn($collection);

        $handler = new EntitiesHandler($this->currentSetting, $this->serializer);
        $this->invokeMethod($handler, 'applyNewEntities', [$entity1, $entity2]);
    }
}
