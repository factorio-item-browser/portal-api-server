<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use DateTimeImmutable;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Mapper\SidebarEntityDataMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SidebarEntityDataMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Mapper\SidebarEntityDataMapper
 */
class SidebarEntityDataMapperTest extends TestCase
{
    /**
     * Tests the getSupportedSourceClass method.
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedSourceClass(): void
    {
        $expectedResult = SidebarEntityData::class;

        $mapper = new SidebarEntityDataMapper();
        $result = $mapper->getSupportedSourceClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getSupportedDestinationClass method.
     * @covers ::getSupportedDestinationClass
     */
    public function testGetSupportedDestinationClass(): void
    {
        $expectedResult = SidebarEntity::class;

        $mapper = new SidebarEntityDataMapper();
        $result = $mapper->getSupportedDestinationClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @covers ::map
     */
    public function testMap(): void
    {
        $lastViewTime = new DateTimeImmutable('2038-01-19 03:14:07');

        $source = new SidebarEntityData();
        $source->setType('abc')
               ->setName('def')
               ->setLabel('ghi')
               ->setPinnedPosition(42)
               ->setLastViewTime($lastViewTime);

        $expectedDestination = new SidebarEntity();
        $expectedDestination->setType('abc')
                            ->setName('def')
                            ->setLabel('ghi')
                            ->setPinnedPosition(42)
                            ->setLastViewTime($lastViewTime);

        $destination = new SidebarEntity();

        $mapper = new SidebarEntityDataMapper();
        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
