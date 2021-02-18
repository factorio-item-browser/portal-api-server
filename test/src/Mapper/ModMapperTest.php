<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use FactorioItemBrowser\Api\Client\Transfer\Mod;
use FactorioItemBrowser\PortalApi\Server\Mapper\ModMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\ModMapper
 */
class ModMapperTest extends TestCase
{
    /**
     * @param array<string> $mockedMethods
     * @return ModMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): ModMapper
    {
        return $this->getMockBuilder(ModMapper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->getMock();
    }

    public function testSupports(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(Mod::class, $instance->getSupportedSourceClass());
        $this->assertSame(ModData::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $source = new Mod();
        $source->name = 'abc';
        $source->label = 'def';
        $source->author = 'ghi';
        $source->version = '1.2.3';

        $expectedDestination = new ModData();
        $expectedDestination->name = 'abc';
        $expectedDestination->label = 'def';
        $expectedDestination->author = 'ghi';
        $expectedDestination->version = '1.2.3';

        $destination = new ModData();

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
