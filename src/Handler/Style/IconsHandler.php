<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Style;

use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleFetcher;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler for providing the styles for icons.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconsHandler implements RequestHandlerInterface
{
    private Setting $currentSetting;
    private IconsStyleFetcher $iconsStyleFetcher;

    public function __construct(
        Setting $currentSetting,
        IconsStyleFetcher $iconsStyleFetcher
    ) {
        $this->currentSetting = $currentSetting;
        $this->iconsStyleFetcher = $iconsStyleFetcher;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var NamesByTypes $namesByTypes */
        $namesByTypes = $request->getParsedBody();

        try {
            $promise = $this->iconsStyleFetcher->request($this->currentSetting, $namesByTypes);
            $iconsStyleData = $this->iconsStyleFetcher->process($promise);
            $this->iconsStyleFetcher->addMissingEntities($iconsStyleData->processedEntities, $namesByTypes);

            return new TransferResponse($iconsStyleData);
        } catch (ClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }
}
