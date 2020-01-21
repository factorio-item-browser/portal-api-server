<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * The type representing an enumeration of values.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractEnumType extends Type
{
    /**
     * The name of the type.
     */
    public const NAME = 'enum';

    /**
     * The values of the enum.
     */
    public const VALUES = ['foo', 'bar'];

    /**
     * Returns the SQL declaration snippet for a field of this type.
     * @param mixed[] $fieldDeclaration The field declaration.
     * @param AbstractPlatform $platform The currently used database platform.
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $quotedValues = implode(',', array_map(function (string $value) use ($platform): string {
            return $platform->quoteStringLiteral(trim($value));
        }, static::VALUES));

        return sprintf('ENUM(%s)', $quotedValues);
    }

    /**
     * Returns the name of this type.
     * @return string
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * Returns whether an SQL comment hint is required.
     * @param AbstractPlatform $platform
     * @return boolean
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
