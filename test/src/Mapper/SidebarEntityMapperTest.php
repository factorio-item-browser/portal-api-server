<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use DateTimeImmutable;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Mapper\SidebarEntityMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * The PHPUnit test of the SidebarEntityMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Mapper\SidebarEntityMapper
 */
class SidebarEntityMapperTest extends TestCase
{
    /**
     * Provides the data for the supports test.
     * @return array<mixed>
     */
    public function provideSupports(): array
    {
        return [
            [new SidebarEntity(), new SidebarEntity(), true],
            [new SidebarEntity(), new SidebarEntityData(), true],
            [new SidebarEntityData(), new SidebarEntity(), true],
            [new SidebarEntityData(), new SidebarEntityData(), true],

            [new SidebarEntity(), new stdClass(), false],
            [new SidebarEntityData(), new stdClass(), false],
            [new stdClass(), new SidebarEntity(), false],
            [new stdClass(), new SidebarEntityData(), false],
        ];
    }

    /**
     * Tests the supports method.
     * @param object $source
     * @param object $destination
     * @param bool $expectedResult
     * @covers ::supports
     * @dataProvider provideSupports
     */
    public function testSupports(object $source, object $destination, bool $expectedResult): void
    {
        $mapper = new SidebarEntityMapper();
        $result = $mapper->supports($source, $destination);

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

        $mapper = new SidebarEntityMapper();
        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
