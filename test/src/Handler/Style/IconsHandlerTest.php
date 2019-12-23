<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Style;

use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\Entity;
use FactorioItemBrowser\Api\Client\Entity\Icon;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericIconRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericIconResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Style\IconsHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleBuilder;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionException;

/**
 * The PHPUnit test of the IconsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Style\IconsHandler
 */
class IconsHandlerTest extends TestCase
{
    use ReflectionTrait;
    
    /**
     * The mocked api client.
     * @var ApiClientInterface&MockObject
     */
    protected $apiClient;
    
    /**
     * The mocked icons style builder.
     * @var IconsStyleBuilder&MockObject
     */
    protected $iconsStyleBuilder;
    
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
        
        $this->apiClient = $this->createMock(ApiClientInterface::class);
        $this->iconsStyleBuilder = $this->createMock(IconsStyleBuilder::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
    }
    
    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new IconsHandler($this->apiClient, $this->iconsStyleBuilder, $this->serializer);
        
        $this->assertSame($this->apiClient, $this->extractProperty($handler, 'apiClient'));
        $this->assertSame($this->iconsStyleBuilder, $this->extractProperty($handler, 'iconsStyleBuilder'));
        $this->assertSame($this->serializer, $this->extractProperty($handler, 'serializer'));
    }
    
    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandle(): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);
        /* @var GenericIconResponse&MockObject $genericIconResponse */
        $genericIconResponse = $this->createMock(GenericIconResponse::class);
        /* @var IconsStyleData&MockObject $iconsStyleData */
        $iconsStyleData = $this->createMock(IconsStyleData::class);
        
        /* @var IconsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(IconsHandler::class)
                        ->onlyMethods(['parseRequestBody', 'fetchData', 'createIconsStyleData'])
                        ->setConstructorArgs([$this->apiClient, $this->iconsStyleBuilder, $this->serializer])
                        ->getMock();
        $handler->expects($this->once())
                ->method('parseRequestBody')
                ->with($this->identicalTo($request))
                ->willReturn($namesByTypes);
        $handler->expects($this->once())
                ->method('fetchData')
                ->with($this->identicalTo($namesByTypes))
                ->willReturn($genericIconResponse);
        $handler->expects($this->once())
                ->method('createIconsStyleData')
                ->with($this->identicalTo($genericIconResponse))
                ->willReturn($iconsStyleData);
        
        /* @var TransferResponse $result */
        $result = $handler->handle($request);
        
        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertSame($iconsStyleData, $result->getTransfer());
    }
    
    /**
     * Tests the parseRequestBody method.
     * @throws ReflectionException
     * @covers ::parseRequestBody
     */
    public function testParseRequestBody(): void
    {
        $requestBody = 'abc';
        
        /* @var NamesByTypes&MockObject $deserializedRequestBody */
        $deserializedRequestBody = $this->createMock(NamesByTypes::class);

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
                             $this->identicalTo(NamesByTypes::class),
                             $this->identicalTo('json')
                         )
                         ->willReturn($deserializedRequestBody);

        $handler = new IconsHandler($this->apiClient, $this->iconsStyleBuilder, $this->serializer);
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
                             $this->identicalTo(NamesByTypes::class),
                             $this->identicalTo('json')
                         )
                         ->willThrowException($this->createMock(Exception::class));

        $this->expectException(InvalidRequestException::class);

        $handler = new IconsHandler($this->apiClient, $this->iconsStyleBuilder, $this->serializer);
        $this->invokeMethod($handler, 'parseRequestBody', $request);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchData(): void
    {
        $namesByTypes = new NamesByTypes();
        $namesByTypes->setValues([
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno']
        ]);

        $entity1 = new Entity();
        $entity1->setType('abc')
                ->setName('def');

        $entity2 = new Entity();
        $entity2->setType('abc')
                ->setName('ghi');

        $entity3 = new Entity();
        $entity3->setType('jkl')
                ->setName('mno');

        $expectedRequest = new GenericIconRequest();
        $expectedRequest->setEntities([$entity1, $entity2, $entity3]);

        /* @var GenericIconResponse&MockObject $response */
        $response = $this->createMock(GenericIconResponse::class);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willReturn($response);

        $handler = new IconsHandler($this->apiClient, $this->iconsStyleBuilder, $this->serializer);
        $result = $this->invokeMethod($handler, 'fetchData', $namesByTypes);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchDataWithException(): void
    {
        $namesByTypes = new NamesByTypes();
        $namesByTypes->setValues([
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno']
        ]);

        $entity1 = new Entity();
        $entity1->setType('abc')
                ->setName('def');

        $entity2 = new Entity();
        $entity2->setType('abc')
                ->setName('ghi');

        $entity3 = new Entity();
        $entity3->setType('jkl')
                ->setName('mno');

        $expectedRequest = new GenericIconRequest();
        $expectedRequest->setEntities([$entity1, $entity2, $entity3]);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willThrowException($this->createMock(ApiClientException::class));

        $this->expectException(FailedApiRequestException::class);

        $handler = new IconsHandler($this->apiClient, $this->iconsStyleBuilder, $this->serializer);
        $this->invokeMethod($handler, 'fetchData', $namesByTypes);
    }

    /**
     * Tests the createIconsStyleData method.
     * @throws ReflectionException
     * @covers ::createIconsStyleData
     */
    public function testCreateIconsStyleData(): void
    {
        $style = 'abc';

        /* @var NamesByTypes&MockObject $processedEntities */
        $processedEntities = $this->createMock(NamesByTypes::class);
        /* @var Icon&MockObject $icon1 */
        $icon1 = $this->createMock(Icon::class);
        /* @var Icon&MockObject $icon2 */
        $icon2 = $this->createMock(Icon::class);

        $genericIconResponse = new GenericIconResponse();
        $genericIconResponse->setIcons([$icon1, $icon2]);

        $expectedResult = new IconsStyleData();
        $expectedResult->setProcessedEntities($processedEntities)
                       ->setStyle($style);

        $this->iconsStyleBuilder->expects($this->exactly(2))
                                ->method('processIcon')
                                ->withConsecutive(
                                    [$this->identicalTo($icon1)],
                                    [$this->identicalTo($icon2)]
                                );
        $this->iconsStyleBuilder->expects($this->once())
                                ->method('getProcessedEntities')
                                ->willReturn($processedEntities);
        $this->iconsStyleBuilder->expects($this->once())
                                ->method('getStyle')
                                ->willReturn($style);

        $handler = new IconsHandler($this->apiClient, $this->iconsStyleBuilder, $this->serializer);
        $result = $this->invokeMethod($handler, 'createIconsStyleData', $genericIconResponse);

        $this->assertEquals($expectedResult, $result);
    }
}
