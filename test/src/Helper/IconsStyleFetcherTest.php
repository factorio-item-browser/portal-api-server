<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Helper;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\Entity;
use FactorioItemBrowser\Api\Client\Entity\Icon;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericIconRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericIconResponse;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleBuilder;
use FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleFetcher;
use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the IconsStyleFetcher class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleFetcher
 */
class IconsStyleFetcherTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked api client factory.
     * @var ApiClientFactory&MockObject
     */
    protected $apiClientFactory;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientFactory = $this->createMock(ApiClientFactory::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $fetcher = new IconsStyleFetcher($this->apiClientFactory);

        $this->assertSame($this->apiClientFactory, $this->extractProperty($fetcher, 'apiClientFactory'));
    }

    /**
     * Tests the request method.
     * @throws ApiClientException
     * @throws ReflectionException
     * @covers ::request
     */
    public function testRequest(): void
    {
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);
        /* @var GenericIconRequest&MockObject $clientRequest */
        $clientRequest = $this->createMock(GenericIconRequest::class);

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('sendRequest')
                  ->with($this->identicalTo($clientRequest));

        $this->apiClientFactory->expects($this->once())
                               ->method('create')
                               ->with($this->identicalTo($setting))
                               ->willReturn($apiClient);

        /* @var IconsStyleFetcher&MockObject $fetcher */
        $fetcher = $this->getMockBuilder(IconsStyleFetcher::class)
                        ->onlyMethods(['createClientRequest'])
                        ->setConstructorArgs([$this->apiClientFactory])
                        ->getMock();
        $fetcher->expects($this->once())
                ->method('createClientRequest')
                ->with($this->identicalTo($namesByTypes))
                ->willReturn($clientRequest);

        $result = $fetcher->request($setting, $namesByTypes);

        $this->assertSame($fetcher, $result);
        $this->assertSame($apiClient, $this->extractProperty($fetcher, 'apiClient'));
        $this->assertSame($clientRequest, $this->extractProperty($fetcher, 'clientRequest'));
        $this->assertInstanceOf(IconsStyleBuilder::class, $this->extractProperty($fetcher, 'iconsStyleBuilder'));
    }

    /**
     * Tests the process method.
     * @throws ApiClientException
     * @throws ReflectionException
     * @covers ::process
     */
    public function testProcess(): void
    {
        /* @var GenericIconRequest&MockObject $clientRequest */
        $clientRequest = $this->createMock(GenericIconRequest::class);
        /* @var GenericIconResponse&MockObject $clientResponse */
        $clientResponse = $this->createMock(GenericIconResponse::class);
        /* @var IconsStyleData&MockObject $iconsStyleData */
        $iconsStyleData = $this->createMock(IconsStyleData::class);

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('fetchResponse')
                  ->with($this->identicalTo($clientRequest))
                  ->willReturn($clientResponse);

        /* @var IconsStyleFetcher&MockObject $fetcher */
        $fetcher = $this->getMockBuilder(IconsStyleFetcher::class)
                        ->onlyMethods(['processClientResponse', 'createIconsStyleData', 'addMissingTypesAndNames'])
                        ->setConstructorArgs([$this->apiClientFactory])
                        ->getMock();
        $fetcher->expects($this->once())
                ->method('processClientResponse')
                ->with($this->identicalTo($clientResponse));
        $fetcher->expects($this->once())
                ->method('createIconsStyleData')
                ->willReturn($iconsStyleData);
        $fetcher->expects($this->once())
                ->method('addMissingTypesAndNames')
                ->with($this->identicalTo($iconsStyleData));
        $this->injectProperty($fetcher, 'apiClient', $apiClient);
        $this->injectProperty($fetcher, 'clientRequest', $clientRequest);

        $result = $fetcher->process();

        $this->assertSame($iconsStyleData, $result);
    }

    /**
     * Tests the createClientRequest method.
     * @throws ReflectionException
     * @covers ::createClientRequest
     */
    public function testCreateClientRequest(): void
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

        $expectedResult = new GenericIconRequest();
        $expectedResult->setEntities([$entity1, $entity2, $entity3]);

        $fetcher = new IconsStyleFetcher($this->apiClientFactory);
        $result = $this->invokeMethod($fetcher, 'createClientRequest', $namesByTypes);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the processClientResponse method.
     * @throws ReflectionException
     * @covers ::processClientResponse
     */
    public function testProcessClientResponse(): void
    {
        /* @var Icon&MockObject $icon1 */
        $icon1 = $this->createMock(Icon::class);
        /* @var Icon&MockObject $icon2 */
        $icon2 = $this->createMock(Icon::class);

        $clientResponse = new GenericIconResponse();
        $clientResponse->setIcons([$icon1, $icon2]);

        /* @var IconsStyleBuilder&MockObject $iconsStyleBuilder */
        $iconsStyleBuilder = $this->createMock(IconsStyleBuilder::class);
        $iconsStyleBuilder->expects($this->exactly(2))
                          ->method('processIcon')
                          ->withConsecutive(
                              [$this->identicalTo($icon1)],
                              [$this->identicalTo($icon2)]
                          );

        $fetcher = new IconsStyleFetcher($this->apiClientFactory);
        $this->injectProperty($fetcher, 'iconsStyleBuilder', $iconsStyleBuilder);

        $this->invokeMethod($fetcher, 'processClientResponse', $clientResponse);
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

        $expectedResult = new IconsStyleData();
        $expectedResult->setProcessedEntities($processedEntities)
                       ->setStyle($style);

        /* @var IconsStyleBuilder&MockObject $iconsStyleBuilder */
        $iconsStyleBuilder = $this->createMock(IconsStyleBuilder::class);
        $iconsStyleBuilder->expects($this->once())
                          ->method('getProcessedEntities')
                          ->willReturn($processedEntities);
        $iconsStyleBuilder->expects($this->once())
                          ->method('getStyle')
                          ->willReturn($style);

        $fetcher = new IconsStyleFetcher($this->apiClientFactory);
        $this->injectProperty($fetcher, 'iconsStyleBuilder', $iconsStyleBuilder);
        $result = $this->invokeMethod($fetcher, 'createIconsStyleData');

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the addMissingTypesAndNames method.
     * @throws ReflectionException
     * @covers ::addMissingTypesAndNames
     */
    public function testAddMissingTypesAndNames(): void
    {
        $entity1 = new Entity();
        $entity1->setType('abc')
                ->setName('def');

        $entity2 = new Entity();
        $entity2->setType('ghi')
                ->setName('jkl');

        /* @var NamesByTypes&MockObject $processedEntities */
        $processedEntities = $this->createMock(NamesByTypes::class);
        $processedEntities->expects($this->exactly(2))
                          ->method('hasValue')
                          ->withConsecutive(
                              [$this->identicalTo('abc'), $this->identicalTo('def')],
                              [$this->identicalTo('ghi'), $this->identicalTo('jkl')]
                          )
                          ->willReturnOnConsecutiveCalls(
                              true,
                              false
                          );
        $processedEntities->expects($this->once())
                          ->method('addValue')
                          ->with($this->identicalTo('ghi'), $this->identicalTo('jkl'));

        /* @var IconsStyleData&MockObject $iconsStyleData */
        $iconsStyleData = $this->createMock(IconsStyleData::class);
        $iconsStyleData->expects($this->once())
                       ->method('getProcessedEntities')
                       ->willReturn($processedEntities);

        $clientRequest = new GenericIconRequest();
        $clientRequest->setEntities([$entity1, $entity2]);

        $fetcher = new IconsStyleFetcher($this->apiClientFactory);
        $this->injectProperty($fetcher, 'clientRequest', $clientRequest);
        $this->invokeMethod($fetcher, 'addMissingTypesAndNames', $iconsStyleData);
    }
}
