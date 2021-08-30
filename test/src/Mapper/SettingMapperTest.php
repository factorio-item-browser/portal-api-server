<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Mapper\SettingMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the SettingMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\SettingMapper
 */
class SettingMapperTest extends TestCase
{
    private string $scriptVersion = 'foo';

    /**
     * @param array<string> $mockedMethods
     * @return SettingMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): SettingMapper
    {
        return $this->getMockBuilder(SettingMapper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->scriptVersion,
                    ])
                    ->getMock();
    }

    public function testSupports(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(Setting::class, $instance->getSupportedSourceClass());
        $this->assertSame(SettingData::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $combinationId = 'bc845964-3422-45ad-b8c1-819af3763667';

        $combination = new Combination();
        $combination->setId(Uuid::fromString($combinationId))
                    ->setStatus('abc');

        $source = new Setting();
        $source->setName('def')
               ->setCombination($combination)
               ->setLocale('ghi')
               ->setRecipeMode('jkl')
               ->setIsTemporary(true);

        $expectedDestination = new SettingData();
        $expectedDestination->combinationId = $combinationId;
        $expectedDestination->combinationHash = 'da68a29bf92996f8';
        $expectedDestination->name = 'def';
        $expectedDestination->locale = 'ghi';
        $expectedDestination->recipeMode = 'jkl';
        $expectedDestination->status = 'abc';
        $expectedDestination->isTemporary = true;

        $destination = new SettingData();

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
