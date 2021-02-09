<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeMachinesResponse;
use FactorioItemBrowser\Api\Client\Transfer\Machine;
use FactorioItemBrowser\PortalApi\Server\Mapper\RecipeMachinesMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\MachineData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeMachinesData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeMachinesMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\RecipeMachinesMapper
 */
class RecipeMachinesMapperTest extends TestCase
{
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;

    protected function setUp(): void
    {
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return RecipeMachinesMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): RecipeMachinesMapper
    {
        $instance = $this->getMockBuilder(RecipeMachinesMapper::class)
                         ->disableProxyingToOriginalMethods()
                         ->onlyMethods($mockedMethods)
                         ->getMock();
        $instance->setMapperManager($this->mapperManager);
        return $instance;
    }

    public function testSupports(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(RecipeMachinesResponse::class, $instance->getSupportedSourceClass());
        $this->assertSame(RecipeMachinesData::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $machine1 = $this->createMock(Machine::class);
        $machine2 = $this->createMock(Machine::class);
        $machineData1 = $this->createMock(MachineData::class);
        $machineData2 = $this->createMock(MachineData::class);

        $source = new RecipeMachinesResponse();
        $source->machines = [$machine1, $machine2];
        $source->totalNumberOfResults = 42;

        $expectedDestination = new RecipeMachinesData();
        $expectedDestination->results = [$machineData1, $machineData2];
        $expectedDestination->numberOfResults = 42;

        $destination = new RecipeMachinesData();

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($machine1), $this->isInstanceOf(MachineData::class)],
                                [$this->identicalTo($machine2), $this->isInstanceOf(MachineData::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $machineData1,
                                $machineData2,
                            );


        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
