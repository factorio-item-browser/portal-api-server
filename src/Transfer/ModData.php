<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The class representing the data of a mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModData
{
    /**
     * The name of the mod.
     * @var string
     */
    protected $name;

    /**
     * The translated label of the mod.
     * @var string
     */
    protected $label;

    /**
     * The author of the mod.
     * @var string
     */
    protected $author;

    /**
     * The version of the mod.
     * @var string
     */
    protected $version;

    /**
     * Sets the name of the mod.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the mod.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the translated label of the mod.
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Returns the translated label of the mod.
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Sets the author of the mod.
     * @param string $author
     * @return $this
     */
    public function setAuthor(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Returns the author of the mod.
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Sets the version of the mod.
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Returns the version of the mod.
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
