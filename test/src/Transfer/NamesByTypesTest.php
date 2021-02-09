<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the NamesByTypes class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes
 */
class NamesByTypesTest extends TestCase
{
    public function testAdd(): void
    {
        $values = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ];
        $expectedValues1 = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno', 'pqr'],
        ];
        $expectedValues2 = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno', 'pqr'],
            'stu' => ['vwx'],
        ];

        $instance = new NamesByTypes();
        $instance->values = $values;

        $this->assertSame($instance, $instance->add('jkl', 'pqr'));
        $this->assertSame($expectedValues1, $instance->values);

        $instance->add('stu', 'vwx');
        $this->assertSame($expectedValues2, $instance->values);
    }
}
