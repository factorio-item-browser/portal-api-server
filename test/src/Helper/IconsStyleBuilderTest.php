<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Helper;

use FactorioItemBrowser\Api\Client\Transfer\Entity;
use FactorioItemBrowser\Api\Client\Transfer\Icon;
use FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleBuilder;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the IconsStyleBuilder class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleBuilder
 */
class IconsStyleBuilderTest extends TestCase
{
    private string $cssSelector = '.foo-{type}-{name}';

    /**
     * @param array<string> $mockedMethods
     * @return IconsStyleBuilder&MockObject
     */
    private function createInstance(array $mockedMethods = []): IconsStyleBuilder
    {
        return $this->getMockBuilder(IconsStyleBuilder::class)
                    ->disableProxyingToOriginalMethods()
                    ->setConstructorArgs([$this->cssSelector])
                    ->onlyMethods($mockedMethods)
                    ->getMock();
    }

    public function testProcessIcon(): void
    {
        $this->cssSelector = '.foo-{type}-{name}';

        $entity1 = new Entity();
        $entity1->type = 'abc';
        $entity1->name = 'def';
        $entity2 = new Entity();
        $entity2->type = 'ghi';
        $entity2->name = 'jkl';
        $entity3 = new Entity();
        $entity3->type = 'abc';
        $entity3->name = 'mno pqr';

        $icon1 = new Icon();
        $icon1->entities = [$entity1, $entity2];
        $icon1->content = 'stu';
        $icon2 = new Icon();
        $icon2->entities = [$entity3];
        $icon2->content = 'vwx';

        $expectedProcessedEntities = new NamesByTypes();
        $expectedProcessedEntities->values = [
            'abc' => ['def', 'mno pqr'],
            'ghi' => ['jkl'],
        ];

        $expectedStyle = <<<EOT
        .foo-abc-def, .foo-ghi-jkl { background-image: url(data:image/png;base64,c3R1); }
        .foo-abc-mno\\5F pqr { background-image: url(data:image/png;base64,dnd4); }
        EOT;

        $instance = $this->createInstance();
        $instance->processIcon($icon1);
        $instance->processIcon($icon2);

        $this->assertEquals($expectedProcessedEntities, $instance->getProcessedEntities());
        $this->assertSame($expectedStyle, $instance->getStyle());
    }
}
