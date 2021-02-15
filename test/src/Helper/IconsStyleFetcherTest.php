<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Helper;

use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericIconRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericIconResponse;
use FactorioItemBrowser\Api\Client\Transfer\Entity;
use FactorioItemBrowser\Api\Client\Transfer\Icon;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleFetcher;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the IconsStyleFetcher class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleFetcher
 */
class IconsStyleFetcherTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $apiClient;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(ClientInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return IconsStyleFetcher&MockObject
     */
    private function createInstance(array $mockedMethods = []): IconsStyleFetcher
    {
        return $this->getMockBuilder(IconsStyleFetcher::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->apiClient,
                    ])
                    ->getMock();
    }

    /**
     * @throws ClientException
     */
    public function testRequest(): void
    {
        $namesByTypes = new NamesByTypes();
        $namesByTypes->add('abc', 'def')
                     ->add('ghi', 'jkl');

        $combination = new Combination();
        $combination->setId(Uuid::fromString('78de8fa6-424b-479e-99c2-bb719eff1e0d'));
        $setting = new Setting();
        $setting->setCombination($combination)
                ->setHasData(true);

        $entity1 = new Entity();
        $entity1->type = 'abc';
        $entity1->name = 'def';
        $entity2 = new Entity();
        $entity2->type = 'ghi';
        $entity2->name = 'jkl';
        $expectedApiRequest = new GenericIconRequest();
        $expectedApiRequest->combinationId = '78de8fa6-424b-479e-99c2-bb719eff1e0d';
        $expectedApiRequest->entities = [$entity1, $entity2];

        $promise = $this->createMock(PromiseInterface::class);

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willReturn($promise);

        $instance = $this->createInstance();
        $result = $instance->request($setting, $namesByTypes);

        $this->assertSame($promise, $result);
    }

    public function testRequestWithoutData(): void
    {
        $namesByTypes = new NamesByTypes();
        $namesByTypes->add('abc', 'def')
                     ->add('ghi', 'jkl');

        $combination = new Combination();
        $combination->setId(Uuid::fromString('78de8fa6-424b-479e-99c2-bb719eff1e0d'));
        $setting = new Setting();
        $setting->setCombination($combination)
                ->setHasData(false);

        $entity1 = new Entity();
        $entity1->type = 'abc';
        $entity1->name = 'def';
        $entity2 = new Entity();
        $entity2->type = 'ghi';
        $entity2->name = 'jkl';
        $expectedApiRequest = new GenericIconRequest();
        $expectedApiRequest->combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $expectedApiRequest->entities = [$entity1, $entity2];

        $promise = $this->createMock(PromiseInterface::class);

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willReturn($promise);

        $instance = $this->createInstance();
        $result = $instance->request($setting, $namesByTypes);

        $this->assertSame($promise, $result);
    }


    public function testProcess(): void
    {
        $entity1 = new Entity();
        $entity1->type = 'abc';
        $entity1->name = 'def';
        $entity2 = new Entity();
        $entity2->type = 'abc';
        $entity2->name = 'ghi';
        $icon1 = new Icon();
        $icon1->entities = [$entity1];
        $icon1->content = 'jkl';
        $icon2 = new Icon();
        $icon2->entities = [$entity2];
        $icon2->content = 'mno';
        $apiResponse = new GenericIconResponse();
        $apiResponse->icons = [$icon1, $icon2];
        $promise = new FulfilledPromise($apiResponse);

        $expectedProcessedEntities = new NamesByTypes();
        $expectedProcessedEntities->values = ['abc' => ['def', 'ghi']];

        $instance = $this->createInstance();
        $result = $instance->process($promise);

        $this->assertEquals($expectedProcessedEntities, $result->processedEntities);
    }

    public function testAddMissingEntities(): void
    {
        $requestedEntities = new NamesByTypes();
        $requestedEntities->values = [
            'abc' => ['def', 'ghi'],
            'mno' => ['pqr'],
        ];

        $processedEntities = new NamesByTypes();
        $processedEntities->values = [
            'abc' => ['def', 'jkl'],
        ];

        $expectedProcessedEntities = new NamesByTypes();
        $expectedProcessedEntities->values = [
            'abc' => ['def', 'jkl', 'ghi'],
            'mno' => ['pqr'],
        ];

        $instance = $this->createInstance();
        $instance->addMissingEntities($processedEntities, $requestedEntities);

        $this->assertEquals($expectedProcessedEntities, $processedEntities);
    }
}
