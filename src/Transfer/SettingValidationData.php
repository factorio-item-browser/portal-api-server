<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing a setting and its validation.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingValidationData
{
    public string $combinationId = '';
    public string $status = '';
    public bool $isValid = true;
    /** @var array<ValidationProblemData> */
    public array $validationProblems = [];
    public ?SettingData $existingSetting = null;
}
