<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\ValidationProblemData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The PHPUnit test of the ValidationProblemData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ValidationProblemDataTest extends SerializerTestCase
{
    private function getObject(): object
    {
        $object = new ValidationProblemData();
        $object->mod = 'abc';
        $object->version = 'def';
        $object->type = 'ghi';
        $object->dependency = 'jkl';
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
    {
        return [
            'mod' => 'abc',
            'version' => 'def',
            'type' => 'ghi',
            'dependency' => 'jkl',
        ];
    }

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
