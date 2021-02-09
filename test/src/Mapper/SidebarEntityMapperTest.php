<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use DateTimeImmutable;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Mapper\SidebarEntityMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SidebarEntityMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\SidebarEntityMapper
 */
class SidebarEntityMapperTest extends TestCase
{
    /**
     * @param array<string> $mockedMethods
     * @return SidebarEntityMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): SidebarEntityMapper
    {
        return $this->getMockBuilder(SidebarEntityMapper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->getMock();
    }

    public function testSupports(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(SidebarEntity::class, $instance->getSupportedSourceClass());
        $this->assertSame(SidebarEntityData::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $lastViewTime = new DateTimeImmutable('2038-01-19 03:14:07');

        $source = new SidebarEntity();
        $source->setType('abc')
               ->setName('def')
               ->setLabel('ghi')
               ->setPinnedPosition(42)
               ->setLastViewTime($lastViewTime);

        $expectedDestination = new SidebarEntityData();
        $expectedDestination->type = 'abc';
        $expectedDestination->name = 'def';
        $expectedDestination->label = 'ghi';
        $expectedDestination->pinnedPosition = 42;
        $expectedDestination->lastViewTime = $lastViewTime;

        $destination = new SidebarEntityData();

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
