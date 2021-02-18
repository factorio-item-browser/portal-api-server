<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use DateTimeImmutable;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Mapper\SidebarEntityDataMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SidebarEntityDataMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\SidebarEntityDataMapper
 */
class SidebarEntityDataMapperTest extends TestCase
{
    /**
     * @param array<string> $mockedMethods
     * @return SidebarEntityDataMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): SidebarEntityDataMapper
    {
        return $this->getMockBuilder(SidebarEntityDataMapper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->getMock();
    }

    public function testSupports(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(SidebarEntityData::class, $instance->getSupportedSourceClass());
        $this->assertSame(SidebarEntity::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $lastViewTime = new DateTimeImmutable('2038-01-19 03:14:07');

        $source = new SidebarEntityData();
        $source->type = 'abc';
        $source->name = 'def';
        $source->label = 'ghi';
        $source->pinnedPosition = 42;
        $source->lastViewTime = $lastViewTime;

        $expectedDestination = new SidebarEntity();
        $expectedDestination->setType('abc')
                            ->setName('def')
                            ->setLabel('ghi')
                            ->setPinnedPosition(42)
                            ->setLastViewTime($lastViewTime);

        $destination = new SidebarEntity();

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
