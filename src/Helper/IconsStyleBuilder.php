<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Helper;

use FactorioItemBrowser\Api\Client\Entity\Icon;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use Laminas\Escaper\Escaper;

/**
 * The class building up the styles used for the icons.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconsStyleBuilder
{
    /**
     * The Zend escaper.
     * @var Escaper
     */
    protected $escaper;

    /**
     * The entities which actually have been processed in the built style.
     * @var NamesByTypes
     */
    protected $processedEntities;

    /**
     * The built stylesheet rules.
     * @var array<string>
     */
    protected $rules = [];

    /**
     * Initializes the builder.
     */
    public function __construct()
    {
        $this->escaper = new Escaper();
        $this->processedEntities = new NamesByTypes();
    }

    /**
     * Processes the icon, building up the style for it.
     * @param Icon $icon
     * @return $this
     */
    public function processIcon(Icon $icon): self
    {
        $selectors = [];
        foreach ($icon->getEntities() as $entity) {
            $selectors[] = $this->buildSelector($entity->getType(), $entity->getName());
            $this->processedEntities->addValue($entity->getType(), $entity->getName());
        }

        $this->rules[] = $this->buildRule($selectors, $icon->getContent());
        return $this;
    }

    /**
     * Builds the selector for the specified type and name.
     * @param string $type
     * @param string $name
     * @return string
     */
    protected function buildSelector(string $type, string $name): string
    {
        $escapedSelector = $this->escaper->escapeCss(str_replace(' ', '_', "{$type}-${name}"));
        return ".icon-{$escapedSelector}";
    }

    /**
     * Builds the rule for the selectors using the content.
     * @param array<string> $selectors
     * @param string $content
     * @return string
     */
    protected function buildRule(array $selectors, string $content): string
    {
        $selector = implode(', ', $selectors);
        $encodedContent = base64_encode($content);

        return "{$selector} { background-image: url(data:image/png;base64,{$encodedContent}); }";
    }

    /**
     * Returns the entities which have been processed.
     * @return NamesByTypes
     */
    public function getProcessedEntities(): NamesByTypes
    {
        return clone $this->processedEntities;
    }

    /**
     * Returns the built style.
     * @return string
     */
    public function getStyle(): string
    {
        return implode(PHP_EOL, $this->rules);
    }
}
