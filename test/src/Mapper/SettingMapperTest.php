<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Mapper\SettingMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use stdClass;

/**
 * The PHPUnit test of the SettingMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\SettingMapper
 */
class SettingMapperTest extends TestCase
{
    /**
     * @param array<string> $mockedMethods
     * @return SettingMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): SettingMapper
    {
        return $this->getMockBuilder(SettingMapper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->getMock();
    }

    /**
     * @return array<mixed>
     */
    public function provideSupports(): array
    {
        return [
            [new Setting(), new SettingMetaData(), true],
            [new Setting(), new SettingDetailsData(), true],
            [new Setting(), new stdClass(), false],
            [new stdClass(), new SettingMetaData(), false],
        ];
    }

    /**
     * @param object $source
     * @param object $destination
     * @param bool $expectedResult
     * @dataProvider provideSupports
     */
    public function testSupports(object $source, object $destination, bool $expectedResult): void
    {
        $instance = $this->createInstance();
        $result = $instance->supports($source, $destination);

        $this->assertSame($expectedResult, $result);
    }

    public function testMap(): void
    {
        $combinationId = 'bc845964-3422-45ad-b8c1-819af3763667';

        $combination = new Combination();
        $combination->setId(Uuid::fromString($combinationId))
                    ->setStatus('def');

        $source = new Setting();
        $source->setName('abc')
               ->setCombination($combination);

        $expectedDestination = new SettingMetaData();
        $expectedDestination->combinationId = $combinationId;
        $expectedDestination->name = 'abc';
        $expectedDestination->status = 'def';

        $destination = new SettingMetaData();

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }

    public function testMapWithDetails(): void
    {
        $combinationId = 'bc845964-3422-45ad-b8c1-819af3763667';

        $combination = new Combination();
        $combination->setId(Uuid::fromString($combinationId))
                    ->setStatus('def');

        $source = new Setting();
        $source->setName('abc')
               ->setCombination($combination)
               ->setLocale('ghi')
               ->setRecipeMode('jkl');

        $expectedDestination = new SettingDetailsData();
        $expectedDestination->combinationId = $combinationId;
        $expectedDestination->name = 'abc';
        $expectedDestination->status = 'def';
        $expectedDestination->locale = 'ghi';
        $expectedDestination->recipeMode = 'jkl';

        $destination = new SettingDetailsData();

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
