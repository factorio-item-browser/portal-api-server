<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\Machine;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeMachinesResponse;
use FactorioItemBrowser\PortalApi\Server\Mapper\RecipeMachinesMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\MachineData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeMachinesData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeMachinesMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Mapper\RecipeMachinesMapper
 */
class RecipeMachinesMapperTest extends TestCase
{
    use ReflectionTrait;

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

        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * Tests the setMapperManager method.
     * @throws ReflectionException
     * @covers ::setMapperManager
     */
    public function testSetMapperManager(): void
    {
        $mapper = new RecipeMachinesMapper();
        $mapper->setMapperManager($this->mapperManager);

        $this->assertSame($this->mapperManager, $this->extractProperty($mapper, 'mapperManager'));
    }

    /**
     * Tests the getSupportedSourceClass method.
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedSourceClass(): void
    {
        $expectedResult = RecipeMachinesResponse::class;

        $mapper = new RecipeMachinesMapper();
        $mapper->setMapperManager($this->mapperManager);

        $result = $mapper->getSupportedSourceClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getSupportedDestinationClass method.
     * @covers ::getSupportedDestinationClass
     */
    public function testGetSupportedDestinationClass(): void
    {
        $expectedResult = RecipeMachinesData::class;

        $mapper = new RecipeMachinesMapper();
        $mapper->setMapperManager($this->mapperManager);

        $result = $mapper->getSupportedDestinationClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @covers ::map
     */
    public function testMap(): void
    {
        /* @var Machine&MockObject $machine1 */
        $machine1 = $this->createMock(Machine::class);
        /* @var Machine&MockObject $machine2 */
        $machine2 = $this->createMock(Machine::class);
        /* @var MachineData&MockObject $machineData1 */
        $machineData1 = $this->createMock(MachineData::class);
        /* @var MachineData&MockObject $machineData2 */
        $machineData2 = $this->createMock(MachineData::class);

        $source = new RecipeMachinesResponse();
        $source->setMachines([$machine1, $machine2])
               ->setTotalNumberOfResults(42);

        $expectedDestination = new RecipeMachinesData();
        $expectedDestination->setResults([$machineData1, $machineData2])
                            ->setNumberOfResults(42);

        $destination = new RecipeMachinesData();

        /* @var RecipeMachinesMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(RecipeMachinesMapper::class)
                       ->onlyMethods(['mapMachine'])
                       ->getMock();
        $mapper->expects($this->exactly(2))
               ->method('mapMachine')
               ->withConsecutive(
                   [$this->identicalTo($machine1)],
                   [$this->identicalTo($machine2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $machineData1,
                   $machineData2
               );
        $mapper->setMapperManager($this->mapperManager);

        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }

    /**
     * Tests the mapMachine method.
     * @throws ReflectionException
     * @covers ::mapMachine
     */
    public function testMapMachine(): void
    {
        /* @var Machine&MockObject $machine */
        $machine = $this->createMock(Machine::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($machine), $this->isInstanceOf(MachineData::class));

        $mapper = new RecipeMachinesMapper();
        $mapper->setMapperManager($this->mapperManager);

        $this->invokeMethod($mapper, 'mapMachine', $machine);
    }
}
