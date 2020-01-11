<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Helper;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\Entity;
use FactorioItemBrowser\Api\Client\Entity\Icon;
use FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleBuilder;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use Laminas\Escaper\Escaper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the IconsStyleBuilder class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleBuilder
 */
class IconsStyleBuilderTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $builder = new IconsStyleBuilder();

        $this->assertInstanceOf(Escaper::class, $this->extractProperty($builder, 'escaper'));
        $this->assertInstanceOf(NamesByTypes::class, $this->extractProperty($builder, 'processedEntities'));
    }
    
    /**
     * Tests the processIcon method.
     * @throws ReflectionException
     * @covers ::processIcon
     */
    public function testProcessIcon(): void
    {
        $entity1 = new Entity();
        $entity1->setType('abc')
                ->setName('def');

        $entity2 = new Entity();
        $entity2->setType('ghi')
                ->setName('jkl');

        $icon = new Icon();
        $icon->setEntities([$entity1, $entity2])
             ->setContent('mno');

        $selector1 = 'pqr';
        $selector2 = 'stu';
        $expectedSelectors = ['pqr', 'stu'];

        $rule = 'vwx';
        $rules = ['foo'];
        $expectedRules = ['foo', 'vwx'];

        /* @var NamesByTypes&MockObject $processedEntities */
        $processedEntities = $this->createMock(NamesByTypes::class);
        $processedEntities->expects($this->exactly(2))
                          ->method('addValue')
                          ->withConsecutive(
                              [$this->identicalTo('abc'), $this->identicalTo('def')],
                              [$this->identicalTo('ghi'), $this->identicalTo('jkl')],
                          );

        /* @var IconsStyleBuilder&MockObject $builder */
        $builder = $this->getMockBuilder(IconsStyleBuilder::class)
                        ->onlyMethods(['buildSelector', 'buildRule'])
                        ->getMock();
        $builder->expects($this->exactly(2))
                ->method('buildSelector')
                ->withConsecutive(
                    [$this->identicalTo('abc'), $this->identicalTo('def')],
                    [$this->identicalTo('ghi'), $this->identicalTo('jkl')]
                )
                ->willReturnOnConsecutiveCalls(
                    $selector1,
                    $selector2
                );
        $builder->expects($this->once())
                ->method('buildRule')
                ->with($this->identicalTo($expectedSelectors), 'mno')
                ->willReturn($rule);

        $this->injectProperty($builder, 'processedEntities', $processedEntities);
        $this->injectProperty($builder, 'rules', $rules);
        
        $result = $builder->processIcon($icon);
        
        $this->assertSame($builder, $result);
        $this->assertSame($expectedRules, $this->extractProperty($builder, 'rules'));
    }

    /**
     * Tests the buildSelector method.
     * @throws ReflectionException
     * @covers ::buildSelector
     */
    public function testBuildSelector(): void
    {
        $type = 'abc def';
        $name = 'ghi jkl';
        $expectedSelector = 'abc_def-ghi_jkl';
        $escapedSelector = 'mno';
        $expectedResult = '.icon-mno';

        /* @var Escaper&MockObject $escaper */
        $escaper = $this->createMock(Escaper::class);
        $escaper->expects($this->once())
                ->method('escapeCss')
                ->with($this->identicalTo($expectedSelector))
                ->willReturn($escapedSelector);

        $builder = new IconsStyleBuilder();
        $this->injectProperty($builder, 'escaper', $escaper);

        $result = $this->invokeMethod($builder, 'buildSelector', $type, $name);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the buildRule method.
     * @throws ReflectionException
     * @covers ::buildRule
     */
    public function testBuildRule(): void
    {
        $selectors = ['.abc', '.def'];
        $content = 'ghi';
        $expectedResult = '.abc, .def { background-image: url(data:image/png;base64,Z2hp); }';

        $builder = new IconsStyleBuilder();
        $result = $this->invokeMethod($builder, 'buildRule', $selectors, $content);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getProcessedEntities method.
     * @throws ReflectionException
     * @covers ::getProcessedEntities
     */
    public function testGetProcessedEntities(): void
    {
        $processedEntities = new NamesByTypes();
        $processedEntities->setValues([
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ]);

        $builder = new IconsStyleBuilder();
        $this->injectProperty($builder, 'processedEntities', $processedEntities);

        $result = $builder->getProcessedEntities();

        $this->assertEquals($processedEntities, $result);
        $this->assertNotSame($processedEntities, $result);
    }

    /**
     * Tests the getStyle method.
     * @throws ReflectionException
     * @covers ::getStyle
     */
    public function testGetStyle(): void
    {
        $rules = ['abc', 'def'];
        $expectedResult = "abc\ndef";

        $builder = new IconsStyleBuilder();
        $this->injectProperty($builder, 'rules', $rules);

        $result = $builder->getStyle();

        $this->assertSame($expectedResult, $result);
    }
}
