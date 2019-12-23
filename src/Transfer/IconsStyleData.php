<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing the style for entity icons.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconsStyleData
{
    /**
     * The processed entities which actually have icons.
     * @var NamesByTypes
     */
    protected $processedEntities;

    /**
     * The style representing the icons of the requested entities.
     * @var string
     */
    protected $style = '';

    /**
     * Initializes the transfer object.
     */
    public function __construct()
    {
        $this->processedEntities = new NamesByTypes();
    }

    /**
     * Sets the processed entities which actually have icons.
     * @param NamesByTypes $processedEntities
     * @return $this
     */
    public function setProcessedEntities(NamesByTypes $processedEntities): self
    {
        $this->processedEntities = $processedEntities;
        return $this;
    }

    /**
     * Returns the processed entities which actually have icons.
     * @return NamesByTypes
     */
    public function getProcessedEntities(): NamesByTypes
    {
        return $this->processedEntities;
    }

    /**
     * Sets the style representing the icons of the requested entities.
     * @param string $style
     * @return $this
     */
    public function setStyle(string $style): self
    {
        $this->style = $style;
        return $this;
    }

    /**
     * Returns the style representing the icons of the requested entities.
     * @return string
     */
    public function getStyle(): string
    {
        return $this->style;
    }
}
