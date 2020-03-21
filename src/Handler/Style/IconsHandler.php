<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Style;

use Exception;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleFetcher;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use JMS\Serializer\SerializerInterface;
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
    /**
     * The current setting.
     * @var Setting
     */
    protected $currentSetting;

    /**
     * The icons style fetcher.
     * @var IconsStyleFetcher
     */
    protected $iconsStyleFetcher;

    /**
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Initializes the handler.
     * @param Setting $currentSetting
     * @param IconsStyleFetcher $iconsStyleFetcher
     * @param SerializerInterface $portalApiServerSerializer
     */
    public function __construct(
        Setting $currentSetting,
        IconsStyleFetcher $iconsStyleFetcher,
        SerializerInterface $portalApiServerSerializer
    ) {
        $this->currentSetting = $currentSetting;
        $this->iconsStyleFetcher = $iconsStyleFetcher;
        $this->serializer = $portalApiServerSerializer;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $namesByTypes = $this->parseRequestBody($request);
        try {
            $this->iconsStyleFetcher->request($this->currentSetting, $namesByTypes);
            $iconsStyleData = $this->iconsStyleFetcher->process();
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        }
        return new TransferResponse($iconsStyleData);
    }

    /**
     * Parses the body of the request.
     * @param ServerRequestInterface $request
     * @return NamesByTypes
     * @throws PortalApiServerException
     */
    protected function parseRequestBody(ServerRequestInterface $request): NamesByTypes
    {
        try {
            $requestBody = $request->getBody()->getContents();
            return $this->serializer->deserialize($requestBody, NamesByTypes::class, 'json');
        } catch (Exception $e) {
            throw new InvalidRequestException($e->getMessage(), $e);
        }
    }
}
