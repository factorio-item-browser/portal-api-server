<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\SettingData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingValidationData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ValidationProblemData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the SettingValidationData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingValidationDataTest extends SerializerTestCase
{
    private function getObject(): object
    {
        $validationProblem1 = new ValidationProblemData();
        $validationProblem1->mod = 'abc';
        $validationProblem1->version = 'def';
        $validationProblem1->type = 'ghi';
        $validationProblem1->dependency = 'jkl';

        $validationProblem2 = new ValidationProblemData();
        $validationProblem2->mod = 'mno';
        $validationProblem2->version = 'pqr';
        $validationProblem2->type = 'stu';
        $validationProblem2->dependency = 'vwx';

        $existingSetting = new SettingData();
        $existingSetting->combinationId = 'yza';
        $existingSetting->combinationHash = 'tuv';
        $existingSetting->name = 'bcd';
        $existingSetting->locale = 'efg';
        $existingSetting->recipeMode = 'hij';
        $existingSetting->status = 'klm';
        $existingSetting->isTemporary = false;

        $object = new SettingValidationData();
        $object->combinationId = 'nop';
        $object->status = 'qrs';
        $object->isValid = true;
        $object->validationProblems = [$validationProblem1, $validationProblem2];
        $object->existingSetting = $existingSetting;
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
    {
        return [
            'combinationId' => 'nop',
            'status' => 'qrs',
            'isValid' => true,
            'validationProblems' => [
                [
                    'mod' => 'abc',
                    'version' => 'def',
                    'type' => 'ghi',
                    'dependency' => 'jkl',
                ],
                [
                    'mod' => 'mno',
                    'version' => 'pqr',
                    'type' => 'stu',
                    'dependency' => 'vwx',
                ],
            ],
            'existingSetting' => [
                'combinationId' => 'yza',
                'combinationHash' => 'tuv',
                'name' => 'bcd',
                'locale' => 'efg',
                'recipeMode' => 'hij',
                'status' => 'klm',
                'isTemporary' => false,
            ],
        ];
    }

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
