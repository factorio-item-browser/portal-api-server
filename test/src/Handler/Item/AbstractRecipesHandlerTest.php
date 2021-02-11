<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Item;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Handler\Item\AbstractRecipesHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemRecipesData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The PHPUnit test of the AbstractRecipesHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Item\AbstractRecipesHandler
 */
class AbstractRecipesHandlerTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $apiClient;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(ClientInterface::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return AbstractRecipesHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): AbstractRecipesHandler
    {
        return $this->getMockBuilder(AbstractRecipesHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->apiClient,
                        $this->mapperManager,
                    ])
                    ->getMockForAbstractClass();
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandle(): void
    {
        $queryParams = [
            'indexOfFirstResult' => '42',
            'numberOfResults' => '21',
        ];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->willReturnMap([
                    ['type', '', 'abc'],
                    ['name', '', 'def']
                ]);
        $request->expects($this->any())
                ->method('getQueryParams')
                ->willReturn($queryParams);

        $item = $this->createMock(GenericEntityWithRecipes::class);
        $transfer = $this->createMock(ItemRecipesData::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($item), $this->isInstanceOf(ItemRecipesData::class))
                            ->willReturn($transfer);

        $instance = $this->createInstance(['fetchData']);
        $instance->expects($this->once())
                 ->method('fetchData')
                 ->with(
                     $this->identicalTo('abc'),
                     $this->identicalTo('def'),
                     $this->identicalTo(42),
                     $this->identicalTo(21),
                 )
                 ->willReturn($item);
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertSame($transfer, $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testFetchDataWithoutItem(): void
    {
        $queryParams = [
            'indexOfFirstResult' => '42',
            'numberOfResults' => '21',
        ];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->willReturnMap([
                    ['type', '', 'abc'],
                    ['name', '', 'def']
                ]);
        $request->expects($this->any())
                ->method('getQueryParams')
                ->willReturn($queryParams);

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $this->expectException(UnknownEntityException::class);

        $instance = $this->createInstance(['fetchData']);
        $instance->expects($this->once())
                 ->method('fetchData')
                 ->with(
                     $this->identicalTo('abc'),
                     $this->identicalTo('def'),
                     $this->identicalTo(42),
                     $this->identicalTo(21),
                 )
                 ->willReturn(null);
        $instance->handle($request);
    }
}
